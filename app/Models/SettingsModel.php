<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table            = 'system_settings';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['setting_key', 'setting_value', 'description'];
    
    /**
     * Helper to get value by key.
     * Returns null if not found.
     */
    public function getValue(string $key)
    {
        $row = $this->where('setting_key', $key)->first();
        return $row ? $row['setting_value'] : null;
    }
}
