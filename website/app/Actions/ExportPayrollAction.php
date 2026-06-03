<?php

namespace App\Actions;

use TCG\Voyager\Actions\AbstractAction;

class ExportPayrollAction extends AbstractAction
{
    public function getTitle()
    {
        return 'Xuất Bảng Lương Excel';
    }

    public function getIcon()
    {
        return 'voyager-download';
    }

    public function getPolicy()
    {
        return 'read';
    }

    public function getAttributes()
    {
        $id = $this->data->{$this->data->getKeyName()};
        
        return [
            'class' => 'btn btn-sm btn-success pull-right', 
            'style' => 'margin-right: 5px;',
            'title' => $this->getTitle(),
            'onclick' => 'exportWithMonths(event, ' . $id . ')'
        ];
    }

    public function getDefaultRoute()
    {
        return 'javascript:;';
    }

    public function shouldActionDisplayOnDataType()
    {
        return $this->dataType->slug == 'nhan-viens'; 
    }
}