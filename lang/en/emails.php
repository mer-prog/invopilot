<?php

return [
    'invoice_subject' => 'Invoice :number',
    'invoice_greeting' => 'Dear :name,',
    'invoice_body' => 'Please find attached invoice :number for :currency :total, due by :due_date.',

    'payment_receipt_subject' => 'Payment Receipt for Invoice :number',
    'payment_receipt_body' => 'We have received your payment of :amount for invoice :number. Thank you.',

    'overdue_subject' => 'Overdue: Invoice :number',
    'overdue_body' => 'Invoice :number was due on :due_date and remains unpaid. Please arrange payment at your earliest convenience.',
];
