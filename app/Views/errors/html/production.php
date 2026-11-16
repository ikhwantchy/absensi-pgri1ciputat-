<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= lang('Errors.whoops') ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; background: #f1f5f9; color: #334155; font: 14px/1.5rem "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif; }
        .card { width: 100%; max-width: 28rem; background: #fff; border-radius: 0.75rem; box-shadow: 0 1px 2px rgb(0 0 0 / 0.06); padding: 1.5rem; text-align: center; }
        .code { margin: 0; font-size: 2.25rem; line-height: 2.5rem; font-weight: 700; color: #2e3192; }
        h1 { margin: 0.5rem 0 0; font-size: 1rem; font-weight: 600; color: #0f172a; }
        p { margin: 0.5rem 0 0; color: #64748b; word-break: break-word; }
        a { display: inline-flex; margin-top: 1.25rem; height: 2.5rem; align-items: center; padding: 0 1rem; border-radius: 0.5rem; background: #2e3192; color: #fff; font-weight: 600; text-decoration: none; }
        a:hover { background: #252877; }
    </style>
</head>
<body>
    <div class="card">
        <p class="code">500</p>
        <h1><?= lang('Errors.whoops') ?></h1>
        <p><?= lang('Errors.weHitASnag') ?></p>
        <a href="/">Kembali ke beranda</a>
    </div>
</body>
</html>
