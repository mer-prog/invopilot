<?php

return [
    'invoice_subject' => '請求書 :number',
    'invoice_greeting' => ':name 様',
    'invoice_body' => '請求書 :number（:currency :total、期日 :due_date）を添付いたしました。ご確認ください。',

    'payment_receipt_subject' => '入金確認：請求書 :number',
    'payment_receipt_body' => '請求書 :number に対する :amount の入金を確認いたしました。ありがとうございます。',

    'overdue_subject' => '期限超過：請求書 :number',
    'overdue_body' => '請求書 :number は :due_date が支払期日でしたが、未払いとなっております。お早めにお支払いください。',
];
