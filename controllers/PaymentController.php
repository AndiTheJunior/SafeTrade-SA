<?php

include '../config/database.php';
include '../models/Payment.php';

class PaymentController
{
    private $pdo;
    private $paymentModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->paymentModel = new Payment($pdo);
    }

    /*
     * Create a pending payment for an order.
     */
    public function createPayment(
        $orderId,
        $buyerId,
        $amount,
        $paymentMethod,
        $transactionReference = null
    )
    {
        return $this->paymentModel->createPayment(
            $orderId,
            $buyerId,
            $amount,
            $paymentMethod,
            $transactionReference
        );
    }

    /*
     * Get payment information for an order.
     */
    public function getPaymentByOrder($orderId)
    {
        return $this->paymentModel->getPaymentByOrder($orderId);
    }

    /*
     * Update a payment status.
     */
    public function updatePaymentStatus($paymentId, $status)
    {
        return $this->paymentModel->updateStatus(
            $paymentId,
            $status
        );
    }

    /*
     * Get all payments for administrators.
     */
    public function getAllPayments()
    {
        return $this->paymentModel->getAllPayments();
    }
}
?>