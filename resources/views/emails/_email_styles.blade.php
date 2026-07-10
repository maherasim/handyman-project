<style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        color: #333;
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
        background-color: #f4f4f4;
    }
    .email-container, .container, .wrapper, .wrap {
        background-color: #ffffff;
        border: 1px solid #ddd;
        overflow: hidden;
    }
    .header {
        background: #ffffff;
        color: #333;
        padding: 20px;
        text-align: center;
        border-bottom: 2px solid #e0e0e0;
    }
    .header h1 { margin: 0; font-size: 22px; font-weight: 600; color: #333; }
    .content { padding: 25px 20px; background: #ffffff; }
    /* All specialty boxes — neutral */
    .success-box, .highlight-box, .hi-box, .info-box, .booking-details,
    .customer-info, .provider-info, .action-box, .status-box, .booking-info,
    .bank-details, .bid-details, .payment-details, .withdrawal-details,
    .subscription-details, .credentials-box, .commission-box, .released-box,
    .requested, .alert-box, .warning-box, .reason-box, .reassurance-box,
    .message-box, .card, .c-card, .plan-details, .attachment-notice {
        background-color: #f8f9fa;
        border-left: 3px solid #999;
        border-radius: 0;
        padding: 15px;
        margin: 15px 0;
    }
    .success-box { border-left-color: #555; }
    .success-box .message { font-size: 16px; font-weight: 600; color: #333; margin-top: 6px; }
    .success-icon { font-size: 28px; }
    /* Info rows */
    .detail-row, .info-row, .bank-row, .cred-row, .breakdown-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }
    .detail-row:last-child, .info-row:last-child, .bank-row:last-child,
    .cred-row:last-child, .breakdown-item:last-child { border-bottom: none; }
    .detail-label, .info-label, .bank-label, .cred-label, .c-label, .r-label {
        font-weight: 600;
        color: #555;
    }
    .detail-value, .info-value, .bank-value, .cred-value, .c-value { color: #333; }
    /* Section headings inside boxes */
    .info-box h3, .booking-details h3, .customer-info h3, .provider-info h3,
    .action-box h3, .status-box h3, .booking-info h3, .payment-details h3,
    .withdrawal-details h3, .subscription-details h3, .bank-details h3,
    .credentials-box h3, .success-box h3 {
        color: #333;
        margin-top: 0;
        font-size: 15px;
    }
    /* Status labels */
    .status-label { font-weight: bold; color: #333; font-size: 16px; }
    /* Buttons — dark neutral */
    .button, .btn, .cta-button {
        display: inline-block;
        padding: 10px 24px;
        background: #444;
        color: #ffffff !important;
        text-decoration: none;
        border-radius: 4px;
        font-weight: 600;
        font-size: 15px;
    }
    .button:hover, .btn:hover, .cta-button:hover { background: #222; }
    .button-container { text-align: center; margin: 20px 0; }
    /* Status badges */
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        background: #eee;
        color: #555;
    }
    .status-accepted, .status-pending, .status-assigned { background: #eee; color: #555; }
    /* Footer */
    .footer {
        background-color: #f8f8f8;
        padding: 15px;
        text-align: center;
        color: #888;
        font-size: 13px;
        border-top: 1px solid #ddd;
    }
    .footer a { color: #555; text-decoration: none; }
    /* Amount/highlights */
    .amount-highlight, .highlight, .amount { color: #333; font-weight: bold; }
    .highlight-box .amount { font-size: 26px; }
    /* Chat sender info */
    .sender-info { display: flex; align-items: center; margin-bottom: 12px; }
    .sender-avatar { width: 40px; height: 40px; border-radius: 50%; background: #ccc; display: flex; align-items: center; justify-content: center; font-size: 18px; margin-right: 10px; }
    .sender-name { font-weight: 600; color: #333; font-size: 15px; }
    .message-content { background: #f8f9fa; border-radius: 4px; padding: 12px 15px; }
    .message-text { color: #333; line-height: 1.5; }
    .message-time { color: #999; font-size: 12px; margin-top: 8px; }
    /* Steps */
    .steps, .instruction-steps { padding: 8px 0; }
    .step { display: flex; align-items: flex-start; margin-bottom: 10px; }
    .step-number, .step-num {
        background: #555;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        text-align: center;
        line-height: 24px;
        font-weight: bold;
        margin-right: 10px;
        flex-shrink: 0;
        font-size: 13px;
    }
    .step-text { color: #333; }
    /* Stars */
    .stars { color: #999; font-size: 20px; }
    .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 12px; background: #eee; color: #555; }
    /* Instructions */
    .instructions { background: #f8f9fa; padding: 15px; border-radius: 4px; }
</style>
