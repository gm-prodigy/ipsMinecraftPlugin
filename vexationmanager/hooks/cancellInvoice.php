//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class vexationmanager_hook_cancellInvoice extends _HOOK_CLASS_
{
	public function unpaid()
	{
		try
		{
			\IPS\Dispatcher::i()->checkAcpPermission( 'invoices_edit' );
			
			/* Load Invoice */
			try
			{
				$invoice = \IPS\nexus\Invoice::load( \IPS\Request::i()->id );
			}
			catch ( \OutOfRangeException $e )
			{
				\IPS\Output::i()->error( 'node_error', '2X190/7', 404, '' );
			}
							
			/* Get paid transactions */
			$transactions = $invoice->transactions( array( \IPS\nexus\Transaction::STATUS_PAID, \IPS\nexus\Transaction::STATUS_PART_REFUNDED ) );
			
			/* Build form */
			$form = new \IPS\Helpers\Form;
			
			/* Ask what we want to do with the transactions */
			if ( \IPS\Member::loggedIn()->hasAcpRestriction( 'nexus', 'payments', 'transactions_refund' ) )
			{
				foreach ( $transactions as $transaction )
				{
					/* What refund options are available? */
					$method = $transaction->method;
					$refundMethods = array();
					$refundMethodToggles = array( 'none' => array( $transaction->id . '_refund_reverse_credit' ) );
					$refundReasons = array();
					if ( $method and $method::SUPPORTS_REFUNDS )
					{
						$refundMethods['gateway'] = 'transaction_refund';
						$refundMethodToggles['gateway'] = array( $transaction->id . '_refund_reverse_credit' );
						if ( $method::SUPPORTS_PARTIAL_REFUNDS )
						{
							$refundMethodToggles['gateway'][] = $transaction->id . '_refund_amount';
						}
						if ( $refundReasons = $method::refundReasons() )
						{
							$refundMethodToggles['gateway'][] = $transaction->id . '_refund_reason';
						}
					}
					if ( $transaction->credit->amount->compare( $transaction->amount->amount ) === -1 )
					{
						$refundMethods['credit'] = 'refund_method_credit';
						$refundMethodToggles['credit'][] = $transaction->id . '_refund_credit_amount';
					}
					$refundMethods['none'] = 'refund_method_none';
					
					/* How do we want to refund? */
					$field = new \IPS\Helpers\Form\Radio( $transaction->id . '_refund_method', 'none', TRUE, array( 'options' => $refundMethods, 'toggles' => $refundMethodToggles ) );
					$field->label = \count( $transactions ) === 1 ? \IPS\Member::loggedIn()->language()->addToStack( 'refund_method' ) : \IPS\Member::loggedIn()->language()->addToStack( 'trans_refund_method', FALSE, array( 'sprintf' => array( $transaction->id ) ) );
					$form->add( $field );
					if ( $refundReasons )
					{
						$field = new \IPS\Helpers\Form\Radio( $transaction->id . '_refund_reason', NULL, FALSE, array( 'options' => $refundReasons ), NULL, NULL, NULL, $transaction->id . '_refund_reason' );
						$field->label = \count( $transactions ) === 1 ? \IPS\Member::loggedIn()->language()->addToStack( 'refund_reason' ) : \IPS\Member::loggedIn()->language()->addToStack( 'trans_refund_reason', FALSE, array( 'sprintf' => array( $transaction->id ) ) );
						$form->add( $field );
					}
					
					/* Partial refund? */
					if ( $method and $method::SUPPORTS_REFUNDS and $method::SUPPORTS_PARTIAL_REFUNDS )
					{
						$field = new \IPS\Helpers\Form\Number( $transaction->id . '_refund_amount', 0, TRUE, array(
							'unlimited' => 0,
							'unlimitedLang'	=> (
								$transaction->partial_refund->amount->isGreaterThanZero()
									? \IPS\Member::loggedIn()->language()->addToStack( 'refund_full_remaining', FALSE, array( 'sprintf' => array(
										new \IPS\nexus\Money( $transaction->amount->amount->subtract( $transaction->partial_refund->amount ), $transaction->currency ) )
									) )
									: \IPS\Member::loggedIn()->language()->addToStack( 'refund_full', FALSE, array( 'sprintf' => array( $transaction->amount ) ) )
							),
							'max'			=> (string) $transaction->amount->amount->subtract( $transaction->partial_refund->amount ),
							'decimals' 		=> TRUE
						), NULL, NULL, $transaction->amount->currency, $transaction->id . '_refund_amount' );
						$field->label = \IPS\Member::loggedIn()->language()->addToStack( 'refund_amount' );
						$form->add( $field );
						if ( $transaction->credit->amount->isGreaterThanZero() )
						{
							\IPS\Member::loggedIn()->language()->words[ $transaction->id . '_refund_amount_desc' ] = sprintf( \IPS\Member::loggedIn()->language()->get('refund_amount_descwarn'), $transaction->credit );
						}
					}
					if ( $transaction->credit->amount->compare( $transaction->amount->amount ) === -1 )
					{
						$field = new \IPS\Helpers\Form\Number( $transaction->id . '_refund_credit_amount', 0, TRUE, array(
							'unlimited'		=> 0,
							'unlimitedLang'	=> (
								$transaction->credit->amount->isGreaterThanZero()
									? \IPS\Member::loggedIn()->language()->addToStack( 'refund_full_remaining', FALSE, array( 'sprintf' => array(
										new \IPS\nexus\Money( $transaction->amount->amount->subtract( $transaction->credit->amount ), $transaction->currency ) )
									) )
									: \IPS\Member::loggedIn()->language()->addToStack( 'refund_full', FALSE, array( 'sprintf' => array( $transaction->amount ) ) )
							),
							'max'			=> (string) $transaction->amount->amount->subtract( $transaction->credit->amount ),
							'decimals' 		=> TRUE
						), NULL, NULL, $transaction->amount->currency, $transaction->id . '_refund_credit_amount' );
						$field->label = \IPS\Member::loggedIn()->language()->addToStack( 'refund_credit_amount' );
						$form->add( $field );
						
						if ( $transaction->partial_refund->amount->isGreaterThanZero() )
						{
							\IPS\Member::loggedIn()->language()->words[ $transaction->id . '_refund_credit_amount_desc' ] = sprintf( \IPS\Member::loggedIn()->language()->get('refund_credit_amount_descwarn'), $transaction->partial_refund );
						}
					}
					
					/* Reverse credit? */
					if ( $transaction->credit->amount->isGreaterThanZero() )
					{
						$field = new \IPS\Helpers\Form\YesNo( $transaction->id . '_refund_reverse_credit', TRUE, TRUE, array( 'togglesOn' => array( "form_{$transaction->id}_refund_reverse_credit_warning" ) ), NULL, NULL, NULL, $transaction->id . '_refund_reverse_credit' );
						$field->label = \IPS\Member::loggedIn()->language()->addToStack( 'refund_reverse_credit', FALSE, array( 'sprintf' => array( $transaction->credit ) ) );
						\IPS\Member::loggedIn()->language()->words[ $transaction->id . '_refund_reverse_credit_desc' ] = \IPS\Member::loggedIn()->language()->addToStack( 'refund_reverse_credit_desc' );
						$form->add( $field );
						
						$credits = $transaction->member->cm_credits;
						if ( $credits[ $transaction->amount->currency ]->amount->compare( $transaction->credit->amount ) === -1 )
						{
							\IPS\Member::loggedIn()->language()->words[ $transaction->id . '_refund_reverse_credit_warning' ] = \IPS\Member::loggedIn()->language()->addToStack( 'account_credit_remove_neg' );
						}
					}
					
					/* Billing Agreement? */
					if ( $billingAgreement = $transaction->billing_agreement )
					{
						$field = new \IPS\Helpers\Form\YesNo( $transaction->id . '_refund_cancel_billing_agreement', TRUE, NULL, array( 'togglesOff' => array( "form_{$transaction->id}_refund_cancel_billing_agreement_warning" ) ) );
						$field->label = \IPS\Member::loggedIn()->language()->addToStack( 'refund_cancel_billing_agreement' );
						\IPS\Member::loggedIn()->language()->words[ $transaction->id . '_refund_cancel_billing_agreement_desc' ] = \IPS\Member::loggedIn()->language()->addToStack( 'refund_cancel_billing_agreement_desc' );
						if ( !\IPS\Db::i()->select( 'COUNT(*)', 'nexus_transactions', array( 't_billing_agreement=? AND t_id<?', $billingAgreement->id, $transaction->id ) )->first() )
						{
							\IPS\Member::loggedIn()->language()->words[ $transaction->id . '_refund_cancel_billing_agreement_warning' ] = \IPS\Member::loggedIn()->language()->addToStack( 'refund_cancel_billing_agreement_warning' );
						}
						
						$form->add( $field );
					}
				}
			}
			
			/* Do we want to mark the invoice as pending or canceled? */
			if ( $invoice->status === \IPS\nexus\Invoice::STATUS_PAID )
			{
				$statusOptions = array();
				if ( !$invoice->total->amount->isZero() )
				{
					$statusOptions[ \IPS\nexus\Invoice::STATUS_PENDING ] = 'refund_invoice_pending';
				}
				if ( \IPS\Settings::i()->cm_invoice_expireafter )
				{
					$statusOptions[ \IPS\nexus\Invoice::STATUS_EXPIRED ] = 'refund_invoice_expired';
				}
				$statusOptions[ \IPS\nexus\Invoice::STATUS_CANCELED ] = 'refund_invoice_canceled';
				$field = new \IPS\Helpers\Form\Radio( 'refund_invoice_status', \IPS\nexus\Invoice::STATUS_CANCELED, TRUE, array( 'options' => $statusOptions ) );
				$field->warningBox = \IPS\Theme::i()->getTemplate('invoices')->unpaidConsequences( $invoice );
				$form->add( $field );
			}
			else
			{
				$statusOptions = array();
				if ( \IPS\Settings::i()->cm_invoice_expireafter )
				{
					$statusOptions[ \IPS\nexus\Invoice::STATUS_EXPIRED ] = 'invoice_status_expd';
				}
				$statusOptions[ \IPS\nexus\Invoice::STATUS_CANCELED ] = 'invoice_status_canc';
				$form->add( new \IPS\Helpers\Form\Radio( 'refund_invoice_status', \IPS\nexus\Invoice::STATUS_CANCELED, TRUE, array( 'options' => $statusOptions ) ) );
			}
	
			/* Handle submissions */
			if ( $values = $form->values() )
			{
				/* Refund transactions */
				if ( \IPS\Member::loggedIn()->hasAcpRestriction( 'nexus', 'payments', 'transactions_refund' ) )
				{
					foreach ( $transactions as $transaction )
					{
						/* Handle billing agreement */
						if ( $transaction->billing_agreement )
						{
							if ( isset( $values[ $transaction->id . '_refund_cancel_billing_agreement' ] ) and $values[ $transaction->id . '_refund_cancel_billing_agreement' ] )
							{
								try
								{
									$transaction->billing_agreement->cancel();
								}
								catch ( \Exception $e )
								{
									\IPS\Output::i()->error( 'billing_agreement_cancel_error', '3X190/G', 500, '', array(), $e->getMessage() );
								}
							}
						}
						/* Reverse credit */
						if ( $values[ $transaction->id . '_refund_method' ] !== 'credit' and isset( $values[ $transaction->id . '_refund_reverse_credit' ] ) and $values[ $transaction->id . '_refund_reverse_credit' ] )
						{
							$transaction->reverseCredit();
						}
						
						/* Refund */
						try
						{
							$amount = NULL;
							if ( $values[ $transaction->id . '_refund_method' ] === 'gateway' and isset( $values[ $transaction->id . '_refund_amount' ] ) )
							{
								$amount = $values[ $transaction->id . '_refund_amount' ];
							}
							elseif ( $values[ $transaction->id . '_refund_method' ] === 'credit' and isset( $values[ $transaction->id . '_refund_credit_amount' ] ) )
							{
								$amount = $values[ $transaction->id . '_refund_credit_amount' ];
							}
							
							$transaction->refund( $values[ $transaction->id . '_refund_method' ], $amount, isset( $values[ $transaction->id . '_refund_reason' ] ) ? $values[ $transaction->id . '_refund_reason' ] : NULL );
						}
						catch ( \LogicException $e )
						{
							\IPS\Output::i()->error( $e->getMessage(), '1X190/1', 500, '' );
						}
						catch ( \RuntimeException $e )
						{
							\IPS\Output::i()->error( 'refund_failed', '3X190/2', 500, '' );
						}
					}
				}
				
				/* Log */
				$invoice->member->log( 'invoice', array(
					'type'	=> 'status',
					'new'	=> $values['refund_invoice_status'],
					'id'	=> $invoice->id,
					'title' => $invoice->title
				) );
				
				/* Change invoice status */
				$invoice->markUnpaid( $values['refund_invoice_status'], \IPS\Member::loggedIn() );
				
				try
				{
					// $member_id=$invoice->member->member_id;
					// try {
					// 	$userInfo = \IPS\Db::i()->select('*', 'minecraft_luckperms_players', array('member_id = ?', $member_id))->first();
						
					// } catch (\UnderflowException $e) {
					// 	$userInfo = array();
					// 	$userInfo['uuid'];
					// 	}
					// 	if (empty($userInfo)) {
					// 		throw new \DomainException( 'vex_no_uuid' );
					// 	}

					// 	$member_uuid = $userInfo['uuid'];

						try {
							require_once(\IPS\ROOT_PATH . '/applications/vexationmanager/interface/vexAPI/WebsenderAPI.php');
						} catch (\RuntimeException $e) {
							throw $e;
						}
			
						// try {
						// 	$vexInfo = \IPS\Db::i()->select('*', 'vexationmanager_settings', array('id = 1'))->first();
			
						// } catch (\UnderflowException $e) {
						// $vexInfo = array();
						// $vexInfo['server_name'] = $vexInfo['host'] = $vexInfo['password'] = $vexInfo['port'] = $vexInfo['updated_at'] = $vexInfo['id'] = null;
						// }
						$host = \IPS\Settings::i()->vexationmanager_host;
						$password = \IPS\Settings::i()->vexationmanager_password;
						$port = \IPS\Settings::i()->vexationmanager_port;
			
						$wsr = new \Websender\WebsenderAPI($host,$password,$port); // HOST , PASSWORD , PORT
						if($wsr->connect()){ //Open Connect          
						$wsr->sendCommand("says user group remove");
						}else{
						// throw new \DomainException('Something went wrong while updating your role');
							throw new \DomainException('Something went wrong while updating role, probably server info needs updating');
						}
					
						$wsr->disconnect(); //Close connection.			
				}
				catch( \UnderflowException $e )
				{
					throw new \DomainException( 'vex_no_uuid' );
				}			
				
				/* Boink */
				$this->_redirect( $invoice );
			}
			
			/* Display */
			\IPS\Output::i()->output = $form;
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}
}
