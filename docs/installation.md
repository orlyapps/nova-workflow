---
title: Base installation
weight: 4
---

Nova Workflow can be installed via Composer:

```bash
composer require "orlyapps/nova-workflow"
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="Orlyapps\NovaWorkflow\NovaWorkflowServiceProvider"
```

Create a new workflow for a Model eg. \App\Models\User

```bash
php artisan make:workflow User
```

To use a workflow with a model, the model must implement the following trait:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orlyapps\NovaWorkßflow\Traits\HasWorkflow;

class User extends Model
{
    use HasWorkflow;
```

```bash
php artisan make:migration "add status to users" --table=users
```
```php
<?php

return new class extends Migration {

    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->default('draft')->nullable();
        });
    }
```
