<?php
	echo $form->create('AuthnetTransaction', array('action' => 'delete'));
	echo $form->input('AuthnetTransaction.transaction_id', array('type' => 'text'));
	echo $form->input('AuthnetTransaction.card_number');
	echo $form->end('Save');
?>
