# HereOTT Redirect Server

This is not a challenge, just a service deployed on a publicly available server. It acts as the poor man's load balancer. All it does is returning a random challenge address across all the available servers. The actual hosts are only accessible from the internal network, but this service is exposed to the public.

### During the contest I ran this using:

```toml
debian@hereott-redirecter:~$ cat ~/.config/systemd/user/container-hereott_redirect.service 
[Unit]
Description=Podman container-hereott_redirect.service
Documentation=man:podman-generate-systemd(1)
Wants=network-online.target
After=network-online.target
RequiresMountsFor=%t/containers

[Service]
Environment=PODMAN_SYSTEMD_UNIT=%n
Restart=on-failure
TimeoutStopSec=70
ExecStartPre=/bin/rm \
        -f %t/%n.ctr-id
ExecStart=/usr/bin/podman run \
        --cidfile=%t/%n.ctr-id \
        --cgroups=no-conmon \
        --rm \
        --sdnotify=conmon \
        --replace \
        -d \
        --name=hereott_redirect \
        --network=slirp4netns:port_handler=slirp4netns \
        -e BACKEND_PORT=48490 \
        -p 48490:48490 ctf-here_ott_redirect:latest
ExecStop=/usr/bin/podman stop \
        --ignore -t 10 \
        --cidfile=%t/%n.ctr-id
ExecStopPost=/usr/bin/podman rm \
        -f \
        --ignore -t 10 \
        --cidfile=%t/%n.ctr-id
Type=notify
NotifyAccess=all

[Install]
```