<?php
echo GWF_Box::box($lang->lang('box_info_purchased', array($order->getCredits(), $user->getCredits())), $lang->lang('box_title_purchased', array($order->getID())));
