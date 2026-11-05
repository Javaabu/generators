---
title: Rolling back changes
sidebar_position: 10
---

To undo what a generator wrote, commit your work first, then run:

```bash
php artisan generate:rollback
```

It runs `git reset`, `git checkout .` and `git clean -fd`: **every uncommitted change is lost**, not only the generated files. It asks before it does; `--force` skips the question.
