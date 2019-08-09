---
title = "Mirroring Github"
date = "2019-08-08 22:15:00 +03:00"
---

# Mirroring Gitea repositories to other hosts

[Gitea](https://gitea.io) is pretty neat if you want a self-hosted GitHub
alternative for most part, even though it is sometimes a bit light on some
features some Git suites provide.

An example I've found useful in the past is [GitLab's][gitlab]
[push mirroring][gitlab-mirroring], which automatically pushes any changes in a
local repo onto a remote one. [^1] Gitea does not natively support this, but
with some effort a `post-receive` [hook][git-hooks] can do nearly [^2] the same.

[^1]: If it's pull mirroring you're looking for, Gitea does have it.
[^2]: A Git hook will run every time a push is performed, whereas GitLab only
      pushes once every five minutes.

[gitlab]: https://gitlab.com
[gitlab-mirroring]: https://docs.gitlab.com/ee/workflow/repository_mirroring.html#pushing-to-a-remote-repository-core
[git-hooks]: https://github.com/git/git/blob/7c20df84bd21ec0215358381844274fa10515017/Documentation/githooks.txt

This tutorial is written with GitHub as the intended mirroring target in mind.
The steps for other Git suites are similar but some particulars might differ.

## Installing

Before installing the hook, you'll need to generate a single-use SSH key and
register that as a write-enabled deploy key on the repository you're intending
to mirror to. I prefer `ed25519` keys, but `rsa` might be more compatible.

From a command line run these commands:

* `cd $(mktemp -d)`
* `ssh-keygen -t ed25519 -f key` (substitude `ed25519` for `rsa` if worried
  about compatibility)
  * When asked for the passphrase, just hit Enter twice.
* `cat key.pub`
  * Copy the output of this command and add this as a deploy key with write
    access. Within GitHub this is within the repository settings > Deploy Keys.
    Check "Allow write access" when doing so.
* `cat key`
  * Note this output, it will be necessary later.
* `rm $PWD; cd -`

Afterwards, go to your Gitea instance and open the repository which you intend
to mirror, go to its settings, open the tab "Git Hooks" and edit the
post-receive hook. If there's something within the "Hook Content" box, delete it
and paste this in:

    #!/bin/sh
    KEY="put your key here"
    REMOTE="put your remote URL here"

    ##########

    keyname=$(mktemp)
    chmod 0600 "$keyname"
    echo "$KEY" >"$keyname"
    chmod 0400 "$keyname"

    knownhosts=$(mktemp)
    echo 'github.com ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEAq2A7hRGmdnm9tUDbO9IDSwBK6TbQa+PXYPCPy6rbTrTtw7PHkccKrpp0yVhp5HdEIcKr6pLlVDBfOLX9QUsyCOV0wzfjIJNlGEYsdlLJizHhbn2mUjvSAHQqZETYP81eFzLQNnPHt4EVVUh7VfDESU84KezmD5QlWpXLmvU31/yMf+Se8xhHTvKSCZIFImWwoG6mbUoWf9nzpIoaSjB+weqqUUmpaaasXVal72J+UX2B+2RPW3RcT0eOzQgqlJL3RKrTJvdsjE3JEAvGq3lGHSZXy28G3skua2SmVi/w4yCE6gbODqnTWlg7+wC604ydGXA8VJiS5ap43JXiUFFAaQ==' >"$knownhosts"
    chmod 0600 "$knownhosts"

    GIT_SSH_COMMAND="ssh -i '$keyname' -o 'CheckHostIP no' -o 'UserKnownHostsFile $knownhosts'" git push --force --mirror "$REMOTE"
    rm "$keyname" "$knownhosts"

Replace the value of the `KEY` variable (within double quotes) with the output
from `cat key` above. It's okay for it to span multiple lines, and the final
quotation mark should be on its own line.

Replace the value of the `REMOTE` variable with the remote URL to mirror unto.
For GitHub it should be something like `git@github.com:username/repo.git`.

So all-in-all, the first couple lines of the hook should look something like
this:

    KEY="-----BEGIN OPENSSH PRIVATE KEY-----
    loremipsumdolorsitametconsecteturadipiscingelitfugitdoloresaperiamquia
    etnonomnisullamconsequunturnumquamquitemporalaboriosamsedilloetcorpori
    inautrepudiandaequiminimadolorquiautemnihiletperspiciatisutetvoluptate
    quiacorporisminimanonvoluptatemquitenetureavoluptasesseconsequaturnamr
    aperiamadiustodoloremexplicabomolestiasnequesedquodqui==
    -----END OPENSSH PRIVATE KEY-----
    "
    REMOTE="git@github.com:username/repo.git"

When that's all done, click "Update Hook" and you should be good to go!

## Looking into it a bit more

The building block of this interaction is the [Git hook][git-hooks], which is
basically a shell script that Git runs whenever some event occurs. Some hooks
allow for modifying Git's behaviour or running code during commit, sync and
other actions, but in this case we just push the changes we receive unto
another repository.

Using ephemeral SSH keys should be considered the best security practice since
the only people who can access them are the ones who have privileges to see the
Git hooks and could modify the repo anyway. The hook itself ensures that it's
unlikely for the key to leak.

As a peculiarity of GitHub, a single deploy key can only be used with a single
repository, so for each repo you're intending to mirror you'll need a new key.
This is better from a security standpoint because a single key being compromised
doesn't grant access to all repositories that might be mirrored using this
technique.

Within the hook there is some `chmod`ding to maintain the key security by
ensuring other UNIX users can't read it. This is required by SSH, otherwise it
will just refuse to use the key at all.

Within the hook the `knownhosts` file is populated as well. This is also a
security precaution by SSH, and you might've encountered it occasionally as the
message `The authenticity of host (..) can't be established. Are you sure you
want to continue connecting?`, and upon confirming the attempt the remote key
will be saved for further connections. As we're running in essentially an
ephemeral context and the remote user can't accept this, the known hosts file
is generated on the fly. [^3]

[^3]: This script contains data for `github.com`; if you wish to use a different
      Git host, their public keys can be obtained by `ssh-keyscan
      remote.hostname`.

For debugging purposes `-vvv` can be given to ssh within `GIT_SSH_COMMAND`.
This will print the entirety of ssh's debugging output to the terminal where
the push takes place.

-----

If you have any questions or concerns, feel free to [contact me][me].

[me]: https://pn.id.lv
