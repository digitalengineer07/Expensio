const axios = require('axios');
const env = require('../config/env');

function findCurrencyCode(text) {
  const upper = text.toUpperCase();
  const matches = upper.match(/\b(USD|EUR|GBP|INR|CAD|AUD|SGD|AED)\b/);
  return matches ? matches[1] : 'INR';
}

function extractMinorUnits(value) {
  const normalized = String(value || '')
    .replace(/,/g, '')
    .replace(/[^0-9.-]/g, '');

  if (!normalized) {
    return null;
  }

  return Math.round(Number(normalized) * 100);
}

function normalizeTaggunResponse(data) {
  const currencyCode = data.currencyCode || findCurrencyCode(data.text || '');
  const items = Array.isArray(data.entities)
    ? data.entities
        .filter((entity) => entity.dataType === 'lineItem')
        .map((entity, index) => ({
          lineNumber: index + 1,
          description: entity.text || 'Line item',
          quantity: Number(entity.quantity || 1),
          unitPriceMinor: extractMinorUnits(entity.unitPrice),
          lineTotalMinor: extractMinorUnits(entity.amount || entity.total || entity.unitPrice || 0),
          currencyCode
        }))
    : [];

  return {
    provider: 'taggun',
    merchantName: data.merchantName || null,
    totalAmountMinor: extractMinorUnits(data.totalAmount || data.totalAmountWithoutTax || 0),
    taxAmountMinor: extractMinorUnits(data.taxAmount || 0),
    currencyCode,
    lineItems: items
  };
}

function normalizeGoogleVisionResponse(data) {
  const text = data.responses?.[0]?.fullTextAnnotation?.text || '';
  const currencyCode = findCurrencyCode(text);
  const lines = text
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean);

  const moneyLines = lines
    .map((line, index) => {
      const amountMatch = line.match(/([0-9]+(?:[.,][0-9]{2})?)$/);
      if (!amountMatch) {
        return null;
      }

      return {
        lineNumber: index + 1,
        description: line.replace(amountMatch[1], '').trim() || 'Line item',
        quantity: 1,
        unitPriceMinor: null,
        lineTotalMinor: extractMinorUnits(amountMatch[1]),
        currencyCode
      };
    })
    .filter(Boolean);

  const lastAmount = moneyLines.length > 0 ? moneyLines[moneyLines.length - 1].lineTotalMinor : null;

  return {
    provider: 'google-vision',
    merchantName: lines[0] || null,
    totalAmountMinor: lastAmount,
    taxAmountMinor: null,
    currencyCode,
    lineItems: moneyLines
  };
}

async function proxyToTaggun(file) {
  const response = await axios.post(env.taggunApiUrl, file.buffer, {
    headers: {
      apikey: env.taggunApiKey,
      'Content-Type': file.mimetype
    },
    maxBodyLength: Infinity
  });

  return normalizeTaggunResponse(response.data);
}

async function proxyToGoogleVision(file) {
  const response = await axios.post(
    `https://vision.googleapis.com/v1/images:annotate?key=${env.googleCloudVisionApiKey}`,
    {
      requests: [
        {
          image: {
            content: file.buffer.toString('base64')
          },
          features: [
            { type: 'DOCUMENT_TEXT_DETECTION' }
          ]
        }
      ]
    }
  );

  return normalizeGoogleVisionResponse(response.data);
}

async function extractReceiptData(req, res) {
  const file = req.file;

  if (!file) {
    return res.status(400).json({ error: 'A receipt image is required.' });
  }

  let normalizedReceipt;
  if (env.ocrProvider === 'google') {
    normalizedReceipt = await proxyToGoogleVision(file);
  } else {
    normalizedReceipt = await proxyToTaggun(file);
  }

  return res.status(200).json(normalizedReceipt);
}

module.exports = {
  extractReceiptData
};
