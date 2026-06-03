<?php

namespace App\Actions;

use TCG\Voyager\Actions\AbstractAction;

class ExportDetailPayrollAction extends AbstractAction
{
    public function getTitle() { return 'Xuất Biên Bản'; }
    
    public function getIcon() { return 'voyager-documentation'; }
    
    public function getPolicy() { return 'read'; }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-info pull-right', 
            'style' => 'margin-right: 5px;',
            'title' => $this->getTitle()
        ];
    }

    public function getDefaultRoute()
    {
        return route('admin.monthly_payrolls.export_detail', $this->data->{$this->data->getKeyName()});
    }

    public function shouldActionDisplayOnDataType()
    {
        return $this->dataType->slug == 'monthly-payrolls'; 
    }
}