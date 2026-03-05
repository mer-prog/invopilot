export type Client = {
    id: number;
    organization_id: number;
    name: string;
    email: string | null;
    phone: string | null;
    company: string | null;
    address: string | null;
    tax_id: string | null;
    notes: string | null;
    created_at: string;
    updated_at: string;
    invoices_count?: number;
    invoices_sum_total?: string | null;
};

export type InvoiceStatus = 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled';

export type InvoiceItem = {
    id?: number;
    description: string;
    quantity: number;
    unit_price: number;
    tax_rate: number;
    amount: number;
    sort_order: number;
};

export type Invoice = {
    id: number;
    organization_id: number;
    client_id: number | null;
    invoice_number: string;
    status: InvoiceStatus;
    issue_date: string;
    due_date: string;
    subtotal: string;
    tax_amount: string;
    discount_amount: string;
    total: string;
    currency: string;
    notes: string | null;
    footer: string | null;
    sent_at: string | null;
    paid_at: string | null;
    cancelled_at: string | null;
    created_at: string;
    updated_at: string;
    client?: Client;
    items?: InvoiceItem[];
};

export type Payment = {
    id: number;
    invoice_id: number;
    amount: string;
    method: string;
    reference: string | null;
    paid_at: string;
    notes: string | null;
    created_at: string;
    updated_at: string;
};

export type PaginatedData<T> = {
    data: T[];
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        per_page: number;
        to: number | null;
        total: number;
    };
};
