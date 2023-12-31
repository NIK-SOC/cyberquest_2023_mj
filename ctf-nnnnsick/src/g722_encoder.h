/*
 * g722.h - The ITU G.722 codec.
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
 * Based on a single channel G.722 codec which is:
 *
 *****    Copyright (c) CMU    1993      *****
 * Computer Science, Speech Group
 * Chengxiang Lu and Alex Hauptmann
 *
 * $Id: g722_encoder.h,v 1.1 2012/08/07 11:33:45 sobomax Exp $
 */


/*! \file */

#pragma once

#include "g722.h"

#ifdef __cplusplus
extern "C" {
#endif

#ifndef _G722_ENC_CTX_DEFINED
typedef void G722_ENC_CTX;
#define _G722_ENC_CTX_DEFINED
#endif

G722_ENC_CTX *g722_encoder_new(int rate, int options);
int g722_encoder_destroy(G722_ENC_CTX *s);
int g722_encode(G722_ENC_CTX *s, const int16_t amp[], int len, uint8_t g722_data[]);

#ifdef __cplusplus
}
#endif
