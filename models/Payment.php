<?php

class Payment
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /*
     * Create a payment record for an order.
     */
    public function createPayment(
        $orderId,
        $buyerId,
        $amount,
        $paymentMethod,
        $transactionReference = null
    )
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO payments
            (
                order_id,
                buyer_id,
                amount,
                payment_method,
                transaction_reference,
                status
            )
            VALUES (?, ?, ?, ?, ?, 'pending')"
        );

        return $stmt->execute([
            $orderId,
            $buyerId,
            $amount,
            $paymentMethod,
            $transactionReference
        ]);
    }

    /*
     * Get a payment by order ID.
     */
    public function getPaymentByOrder($orderId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT *
             FROM payments
             WHERE order_id = ?
             ORDER BY created_at DESC
             LIMIT 1"
        );

        $stmt->execute([
            $orderId
        ]);

        return $stmt->fetch();
    }

    /*
     * Update payment status.
     */
    public function updateStatus($paymentId, $status)
    {
        $allowedStatuses = [
            'pending',
            'paid',
            'failed',
            'refunded'
        ];

        if(!in_array($status, $allowedStatuses))
        {
            return false;
        }

        $stmt = $this->pdo->prepare(
            "UPDATE payments
             SET status = ?
             WHERE id = ?"
        );

        return $stmt->execute([
            $status,
            $paymentId
        ]);
    }

    /*
     * Get all payments.
     */
    public function getAllPayments()
    {
        $stmt = $this->pdo->prepare(
            "SELECT payments.*,
                    orders.product_id,
                    orders.seller_id,
                    products.title AS product_title,
                    buyers.fullname AS buyer_name,
                    sellers.fullname AS seller_name
             FROM payments

             INNER JOIN orders
                ON payments.order_id = orders.id

             INNER JOIN products
                ON orders.product_id = products.id

             INNER JOIN users AS buyers
                ON payments.buyer_id = buyers.id

             INNER JOIN users AS sellers
                ON orders.seller_id = sellers.id

             ORDER BY payments.created_at DESC"
        );

        $stmt->execute();

        return $stmt;
    }
}
?>