const express = require('express');
const asyncHandler = require('../middleware/asyncHandler');
const requireAuth = require('../middleware/requireAuth');
const settlementController = require('../controllers/groupSettlementController');
const invitationController = require('../controllers/invitationController');

const router = express.Router();

router.post('/:groupId/simplify', requireAuth, asyncHandler(settlementController.simplifyGroupDebts));
router.post('/:groupId/invitations', requireAuth, asyncHandler(invitationController.createGroupInvitation));
router.post('/invitations/:token/accept', requireAuth, asyncHandler(invitationController.acceptGroupInvitation));

module.exports = router;
