/*
 * SpanDSP - a series of DSP components for telephony
 *
 * g722_decode.c - The ITU G.722 codec, decode part.
 *
 * Written by Steve Underwood <steveu@coppice.org>
 *
 * Copyright (C) 2005 Steve Underwood
 *
 *  Despite my general liking of the GPL, I place my own contributions 
 *  to this code in the public domain for the benefit of all mankind -
 *  even the slimy ones who might try to proprietize my work and use it
 *  to my detriment.
 *
 * Based in part on a single channel G.722 codec which is:
 *
 * Copyright (c) CMU 1993
 * Computer Science, Speech Group
 * Chengxiang Lu and Alex Hauptmann
 * 
 * The Carnegie Mellon ADPCM program is Copyright (c) 1993 by Carnegie Mellon
 * University. Use of this program, for any research or commercial purpose, is
 * completely unrestricted. If you make use of or redistribute this material,
 * we would appreciate acknowlegement of its origin.
 */

/*! \file */

#include <stdio.h>
#include <inttypes.h>
#include <memory.h>
#include <stdlib.h>

#include "g722_private.h"
#include "g722_common.h"
#include "g722.h"
#include "g722_decoder.h"

G722_DEC_CTX *g722_decoder_new(int rate, int options)
{
    G722_DEC_CTX *s;

    if ((s = (G722_DEC_CTX *) malloc(sizeof(*s))) == NULL)
        return NULL;
    memset(s, 0, sizeof(*s));
    if (rate == 48000)
        s->bits_per_sample = 6;
    else if (rate == 56000)
        s->bits_per_sample = 7;
    else
        s->bits_per_sample = 8;
    if ((options & G722_SAMPLE_RATE_8000))
        s->eight_k = TRUE;
    if ((options & G722_PACKED)  &&  s->bits_per_sample != 8)
        s->packed = TRUE;
    else
        s->packed = FALSE;
    s->band[0].det = 32;
    s->band[1].det = 8;
    return s;
}
/*- End of function --------------------------------------------------------*/

int g722_decoder_destroy(G722_DEC_CTX *s)
{
    free(s);
    return 0;
}
/*- End of function --------------------------------------------------------*/

int g722_decode(G722_DEC_CTX *s, const uint8_t g722_data[], int len, int16_t amp[])
{
    static const int wl[8] = {-60, -30, 58, 172, 334, 538, 1198, 3042 };
    static const int rl42[16] = {0, 7, 6, 5, 4, 3, 2, 1, 7, 6, 5, 4, 3,  2, 1, 0 };
    static const int ilb[32] =
    {
        2048, 2093, 2139, 2186, 2233, 2282, 2332,
        2383, 2435, 2489, 2543, 2599, 2656, 2714,
        2774, 2834, 2896, 2960, 3025, 3091, 3158,
        3228, 3298, 3371, 3444, 3520, 3597, 3676,
        3756, 3838, 3922, 4008
    };
    static const int wh[3] = {0, -214, 798};
    static const int rh2[4] = {2, 1, 2, 1};
    static const int qm2[4] = {-7408, -1616,  7408,   1616};
    static const int qm4[16] = 
    {
              0, -20456, -12896,  -8968, 
          -6288,  -4240,  -2584,  -1200,
          20456,  12896,   8968,   6288,
           4240,   2584,   1200,      0
    };
    static const int qm5[32] =
    {
           -280,   -280, -23352, -17560,
         -14120, -11664,  -9752,  -8184,
          -6864,  -5712,  -4696,  -3784,
          -2960,  -2208,  -1520,   -880,
          23352,  17560,  14120,  11664,
           9752,   8184,   6864,   5712,
           4696,   3784,   2960,   2208,
           1520,    880,    280,   -280
    };
    static const int qm6[64] =
    {
           -136,   -136,   -136,   -136,
         -24808, -21904, -19008, -16704,
         -14984, -13512, -12280, -11192,
         -10232,  -9360,  -8576,  -7856,
          -7192,  -6576,  -6000,  -5456,
          -4944,  -4464,  -4008,  -3576,
          -3168,  -2776,  -2400,  -2032,
          -1688,  -1360,  -1040,   -728,
          24808,  21904,  19008,  16704,
          14984,  13512,  12280,  11192,
          10232,   9360,   8576,   7856,
           7192,   6576,   6000,   5456,
           4944,   4464,   4008,   3576,
           3168,   2776,   2400,   2032,
           1688,   1360,   1040,    728,
            432,    136,   -432,   -136
    };
    static const int qmf_coeffs[12] =
    {
           3,  -11,   12,   32, -210,  951, 3876, -805,  362, -156,   53,  -11,
    };

    int dlowt;
    int rlow;
    int ihigh;
    int dhigh;
    int rhigh;
    int xout1;
    int xout2;
    int wd1;
    int wd2;
    int wd3;
    int code;
    int outlen;
    int i;
    int j;

    outlen = 0;
    rhigh = 0;
    for (j = 0;  j < len;  )
    {
        if (s->packed)
        {
            /* Unpack the code bits */
            if (s->in_bits < s->bits_per_sample)
            {
                s->in_buffer |= (g722_data[j++] << s->in_bits);
                s->in_bits += 8;
                printf("in_buffer: %d\n", s->in_buffer);
            }
            code = s->in_buffer & ((1 << s->bits_per_sample) - 1);
            printf("code: %d\n", code);
            s->in_buffer >>= s->bits_per_sample;
            printf("in_buffer: %d\n", s->in_buffer);
            s->in_bits -= s->bits_per_sample;
            printf("in_bits: %d\n", s->in_bits);
        }
        else
        {
            code = g722_data[j++];
            printf("code: %d\n", code);
        }

        switch (s->bits_per_sample)
        {
        default:
        case 8:
            wd1 = code & 0x3F;
            printf("wd1: %d\n", wd1);
            ihigh = (code >> 6) & 0x03;
            printf("ihigh: %d\n", ihigh);
            wd2 = qm6[wd1];
            printf("wd2: %d\n", wd2);
            wd1 >>= 2;
            printf("wd1: %d\n", wd1);
            break;
        case 7:
            wd1 = code & 0x1F;
            printf("wd1: %d\n", wd1);
            ihigh = (code >> 5) & 0x03;
            printf("ihigh: %d\n", ihigh);
            wd2 = qm5[wd1];
            printf("wd2: %d\n", wd2);
            wd1 >>= 1;
            printf("wd1: %d\n", wd1);
            break;
        case 6:
            wd1 = code & 0x0F;
            printf("wd1: %d\n", wd1);
            ihigh = (code >> 4) & 0x03;
            printf("ihigh: %d\n", ihigh);
            wd2 = qm4[wd1];
            printf("wd2: %d\n", wd2);
            break;
        }
        /* Block 5L, LOW BAND INVQBL */
        wd2 = (s->band[0].det*wd2) >> 15;
        printf("wd2: %d\n", wd2);
        /* Block 5L, RECONS */
        rlow = s->band[0].s + wd2;
        printf("rlow: %d\n", rlow);
        /* Block 6L, LIMIT */
        if (rlow > 16383) {
            rlow = 16383;
            printf("rlow: %d\n", rlow);
        }
        else if (rlow < -16384) {
            rlow = -16384;
            printf("rlow: %d\n", rlow);
        }

        /* Block 2L, INVQAL */
        wd2 = qm4[wd1];
        printf("wd2: %d\n", wd2);
        dlowt = (s->band[0].det*wd2) >> 15;
        printf("dlowt: %d\n", dlowt);

        /* Block 3L, LOGSCL */
        wd2 = rl42[wd1];
        printf("wd2: %d\n", wd2);
        wd1 = (s->band[0].nb*127) >> 7;
        printf("wd1: %d\n", wd1);
        wd1 += wl[wd2];
        printf("wd1: %d\n", wd1);
        if (wd1 < 0) {
            wd1 = 0;
            printf("wd1: %d\n", wd1);
        }
        else if (wd1 > 18432) {
            wd1 = 18432;
            printf("wd1: %d\n", wd1);
        }
        s->band[0].nb = wd1;
        printf("s->band[0].nb: %d\n", s->band[0].nb);
            
        /* Block 3L, SCALEL */
        wd1 = (s->band[0].nb >> 6) & 31;
        printf("wd1: %d\n", wd1);
        wd2 = 8 - (s->band[0].nb >> 11);
        printf("wd2: %d\n", wd2);
        wd3 = (wd2 < 0)  ?  (ilb[wd1] << -wd2)  :  (ilb[wd1] >> wd2);
        printf("wd3: %d\n", wd3);
        s->band[0].det = wd3 << 2;
        printf("s->band[0].det: %d\n", s->band[0].det);

        block4(&s->band[0], dlowt);
        printf("s->band[0].s: %d\n", s->band[0].s);
        
        if (!s->eight_k)
        {
            /* Block 2H, INVQAH */
            wd2 = qm2[ihigh];
            printf("wd2: %d\n", wd2);
            dhigh = (s->band[1].det*wd2) >> 15;
            printf("dhigh: %d\n", dhigh);
            /* Block 5H, RECONS */
            rhigh = dhigh + s->band[1].s;
            printf("rhigh: %d\n", rhigh);
            /* Block 6H, LIMIT */
            if (rhigh > 16383) {
                rhigh = 16383;
                printf("rhigh: %d\n", rhigh);
            }
            else if (rhigh < -16384) {
                rhigh = -16384;
                printf("rhigh: %d\n", rhigh);
            }

            /* Block 2H, INVQAH */
            wd2 = rh2[ihigh];
            printf("wd2: %d\n", wd2);
            wd1 = (s->band[1].nb*127) >> 7;
            printf("wd1: %d\n", wd1);
            wd1 += wh[wd2];
            printf("wd1: %d\n", wd1);
            if (wd1 < 0) {
                wd1 = 0;
                printf("wd1: %d\n", wd1);
            }
            else if (wd1 > 22528) {
                wd1 = 22528;
                printf("wd1: %d\n", wd1);
            }
            s->band[1].nb = wd1;
            printf("s->band[1].nb: %d\n", s->band[1].nb);
            
            /* Block 3H, SCALEH */
            wd1 = (s->band[1].nb >> 6) & 31;
            printf("wd1: %d\n", wd1);
            wd2 = 10 - (s->band[1].nb >> 11);
            printf("wd2: %d\n", wd2);
            wd3 = (wd2 < 0)  ?  (ilb[wd1] << -wd2)  :  (ilb[wd1] >> wd2);
            printf("wd3: %d\n", wd3);
            s->band[1].det = wd3 << 2;
            printf("s->band[1].det: %d\n", s->band[1].det);

            block4(&s->band[1], dhigh);
            printf("s->band[1].s: %d\n", s->band[1].s);
        }

        if (s->itu_test_mode)
        {
            amp[outlen++] = (int16_t) (rlow << 1);
            printf("amp[%d]: %d\n", outlen - 1, amp[outlen - 1]);
            amp[outlen++] = (int16_t) (rhigh << 1);
            printf("amp[%d]: %d\n", outlen - 1, amp[outlen - 1]);
        }
        else
        {
            if (s->eight_k)
            {
                amp[outlen++] = (int16_t) (rlow << 1);
                printf("amp[%d]: %d\n", outlen - 1, amp[outlen - 1]);
            }
            else
            {
                /* Apply the receive QMF */
                for (i = 0;  i < 22;  i++) {
                    s->x[i] = s->x[i + 2];
                    printf("s->x[%d]: %d\n", i, s->x[i]);
                }
                s->x[22] = rlow + rhigh;
                printf("s->x[22]: %d\n", s->x[22]);
                s->x[23] = rlow - rhigh;
                printf("s->x[23]: %d\n", s->x[23]);

                xout1 = 0;
                xout2 = 0;
                for (i = 0;  i < 12;  i++)
                {
                    xout2 += s->x[2*i]*qmf_coeffs[i];
                    printf("xout2: %d\n", xout2);
                    xout1 += s->x[2*i + 1]*qmf_coeffs[11 - i];
                    printf("xout1: %d\n", xout1);
                }
                amp[outlen++] = saturate(xout1 >> 11);
                printf("amp[%d]: %d\n", outlen - 1, amp[outlen - 1]);
                amp[outlen++] = saturate(xout2 >> 11);
                printf("amp[%d]: %d\n", outlen - 1, amp[outlen - 1]);
            }
        }
    }
    printf("outlen: %d\n", outlen);
    return outlen;
}
/*- End of function --------------------------------------------------------*/
/*- End of file ------------------------------------------------------------*/
