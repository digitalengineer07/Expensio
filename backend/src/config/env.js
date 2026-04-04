const dotenv = require('dotenv');

dotenv.config();

module.exports = {
  port: Number(process.env.PORT || 4000),
  appOrigin: process.env.APP_ORIGIN || 'http://localhost:3000',
  databaseUrl: process.env.DATABASE_URL,
  jwtSecret: process.env.JWT_SECRET || 'development-secret',
  jwtExpiresIn: process.env.JWT_EXPIRES_IN || '15m',
  ocrProvider: (process.env.OCR_PROVIDER || 'taggun').toLowerCase(),
  taggunApiKey: process.env.TAGGUN_API_KEY || '',
  taggunApiUrl:
    process.env.TAGGUN_API_URL || 'https://api.taggun.io/api/receipt/v1/verbose/file',
  googleCloudVisionApiKey: process.env.GOOGLE_CLOUD_VISION_API_KEY || '',
  invitationTtlHours: Number(process.env.INVITATION_TTL_HOURS || 168)
};
