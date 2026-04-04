const express = require('express');
const multer = require('multer');
const asyncHandler = require('../middleware/asyncHandler');
const receiptOcrController = require('../controllers/receiptOcrController');

const router = express.Router();
const upload = multer({
  storage: multer.memoryStorage(),
  limits: {
    fileSize: 10 * 1024 * 1024
  }
});

router.post('/ocr', upload.single('receipt'), asyncHandler(receiptOcrController.extractReceiptData));

module.exports = router;
