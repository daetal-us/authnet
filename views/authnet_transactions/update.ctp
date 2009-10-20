<?php
	echo $form->create('AuthnetTransaction', array('action' => 'update'));
		echo $form->input('AuthnetTransaction.transaction_id', array('label' => 'Transaction ID', 'type' => 'text'));
	echo $form->input('AuthnetTransaction.amount');
	echo $form->input('AuthnetTransaction.card_number');
	echo $form->end('Save');
?>
<h2>Notes</h2>
<p>You can capture a previously authorized transacton, or issue a credit against a previously captured and settled one. If you're trying to refund a transaction that was captured but not yet settled, you want to void the transaction. See <?php echo $html->link('delete', array('action' => 'delete')); ?>.</p>
<p>The system decides to do one or the other depending on whether the amount is negative (Credit) or greater than or equal to zero (Prior Auth Capture).</p>

<h3>Capture Previously Authorized Transactions</h3>
<p>The payment gateway accepts this transaction type and initiates settlement if the following conditions are met:</p>

<ul>
	<li>The original Authorization Only transaction was submitted within the previous 30 days (Authorization Only transactions expire on the payment gateway after 30 days).</li>
	<li>The transaction is submitted with the valid Transaction ID of an original, successfully authorized, Authorization Only transaction.</li>
	<li>The original transaction is not yet captured, expired or errored.</li>
	<li>The amount being requested for capture is less than or equal to the original authorized amount. Please note that only a single Prior Authorization and Capture transaction may be submitted against an Authorization Only.</li>
</ul>
<p><strong>Required field(s):</strong> Transaction Id</p>
<p><strong>Optional field(s):</strong> Amount</p>

<h3>Credit</h3>
<p>This transaction type is used to refund a customer for a transaction that was originally processed and successfully settled through the payment gateway.</p>
<p>The payment gateway accepts Credits if the following conditions are met:</p>

<ul>
	<li>The transaction is submitted with the valid Transaction ID of an original, successfully settled transaction.</li>
	<li>The amount being requested for refund is less than or equal to the original settled amount.</li>
	<li>The sum amount of multiple Credit transactions submitted against the original transaction is less than or equal to the original settled amount.</li>
	<li>At least the last four digits of the credit card number (x_card_num) used for the original, successfully settled transaction are submitted. An expiration date is not required.</li>
	<li>The transaction is submitted within 120 days of the settlement date of the original transaction.</li>
</ul>

<p><strong>Required field(s):</strong> Transaction Id, Card Number</p>
<p><strong>Optional field(s):</strong> Amount</p>
