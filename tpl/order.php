<?php
$order instanceof GWF_CreditsOrder;

echo GWF_Table::start();

echo GWF_Table::rowStart();
echo GWF_Table::column($lang->lang('th_current_credits'), 'gwf-label');
echo GWF_Table::column($order->getOldCredits(), 'gwf-num');
echo GWF_Table::rowEnd();

echo GWF_Table::rowStart();
echo GWF_Table::column($lang->lang('th_ordered_credits'), 'gwf-label');
echo GWF_Table::column($order->getCredits(), 'gwf-num');
echo GWF_Table::rowEnd();

echo GWF_Table::rowStart();
echo GWF_Table::column($lang->lang('th_final_credits'), 'gwf-label');
echo GWF_Table::column($order->getCredits()+$order->getOldCredits(), 'gwf-num');
echo GWF_Table::rowEnd();

echo GWF_Table::end();
