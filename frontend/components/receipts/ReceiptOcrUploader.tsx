'use client';

import { useState } from 'react';
import { Receipt } from 'lucide-react';

const apiBaseUrl = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:4000';

type ReceiptPayload = {
  provider: string;
  merchantName: string | null;
  totalAmountMinor: number | null;
  currencyCode: string;
  lineItems: Array<{
    lineNumber: number;
    description: string;
    quantity: number;
    lineTotalMinor: number | null;
  }>;
};

function formatCurrency(minor: number | null, currencyCode: string) {
  if (minor === null) {
    return 'Pending';
  }

  return new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: currencyCode,
    maximumFractionDigits: 2
  }).format(minor / 100);
}

export function ReceiptOcrUploader() {
  const [result, setResult] = useState<ReceiptPayload | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isUploading, setIsUploading] = useState(false);

  async function handleFileChange(event: React.ChangeEvent<HTMLInputElement>) {
    const file = event.target.files?.[0];
    if (!file) {
      return;
    }

    setIsUploading(true);
    setError(null);

    const formData = new FormData();
    formData.append('receipt', file);

    const response = await fetch(`${apiBaseUrl}/api/receipts/ocr`, {
      method: 'POST',
      body: formData
    });

    const data = await response.json();
    setIsUploading(false);

    if (!response.ok) {
      setError(data.error || 'Unable to read receipt.');
      return;
    }

    setResult(data);
  }

  return (
    <div className="space-y-4">
      <label className="flex cursor-pointer flex-col items-center justify-center rounded-[28px] border border-dashed border-line bg-cloud px-6 py-10 text-center transition hover:border-success hover:bg-success-soft/40">
        <span className="rounded-2xl bg-white p-3 text-success shadow-sm">
          <Receipt className="h-5 w-5" />
        </span>
        <span className="mt-4 text-lg font-semibold text-ink">Upload receipt image</span>
        <span className="mt-2 text-sm text-ink-soft">JPG, PNG, or HEIC up to 10MB</span>
        <input className="hidden" type="file" accept="image/*" onChange={handleFileChange} />
      </label>

      {isUploading && <p className="text-sm text-ink-soft">Reading receipt and extracting totals...</p>}
      {error && <p className="text-sm text-red-600">{error}</p>}

      {result && (
        <div className="space-y-4 rounded-[28px] border border-line bg-cloud p-4">
          <div className="flex items-start justify-between gap-4">
            <div>
              <p className="text-sm text-ink-soft">Merchant</p>
              <h3 className="text-lg font-semibold text-ink">{result.merchantName || 'Receipt draft'}</h3>
            </div>
            <span className="rounded-full bg-success-soft px-3 py-1 text-sm font-semibold text-success">
              {result.provider}
            </span>
          </div>
          <div className="rounded-2xl bg-white p-4">
            <p className="text-sm text-ink-soft">Detected total</p>
            <p className="mt-1 text-2xl font-bold text-ink">
              {formatCurrency(result.totalAmountMinor, result.currencyCode)}
            </p>
          </div>
          <div className="space-y-3">
            {result.lineItems.slice(0, 5).map((item) => (
              <div key={item.lineNumber} className="flex items-center justify-between rounded-2xl bg-white px-4 py-3 text-sm">
                <div>
                  <p className="font-medium text-ink">{item.description}</p>
                  <p className="text-ink-soft">Qty {item.quantity}</p>
                </div>
                <span className="font-semibold text-ink">
                  {formatCurrency(item.lineTotalMinor, result.currencyCode)}
                </span>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}

