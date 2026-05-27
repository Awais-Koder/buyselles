# Taste (Continuously Learned by [CommandCode][cmd])

[cmd]: https://commandcode.ai/

# blade-views
- Do not display existing digital product code tables (serial numbers, card codes) on product update/edit forms — keep forms focused on input fields only to avoid UI clutter with large code pools. Confidence: 0.70

# laravel
- When building Artisan commands that truncate multiple database tables, verify each table exists (e.g., via `Schema::hasTable()`) before calling `truncate()` to avoid SQLSTATE[42S02] runtime errors from tables that may not actually be in the schema. Confidence: 0.55

