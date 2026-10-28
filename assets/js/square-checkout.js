(() => {
    const checkoutForm = document.getElementById('checkout-form');
    const checkoutSubmitButton = document.getElementById('checkout-submit-button');
    const squareCardContainer = document.getElementById('square-card-container');
    const squarePaymentMessage = document.getElementById('square-payment-message');
    const totalDisplay = document.getElementById('checkout-total');
    const hasItems = checkoutSubmitButton && !checkoutSubmitButton.disabled;

    if (!checkoutForm || !checkoutSubmitButton || !squarePaymentMessage) {
        return;
    }

    let squareCard = null;

    const setPaymentMessage = (message, isError = false) => {
        squarePaymentMessage.textContent = message;
        squarePaymentMessage.classList.toggle('text-danger', isError);
        squarePaymentMessage.classList.toggle('text-success', !isError && message !== '');
    };

    const initializeSquareCard = async () => {
        checkoutSubmitButton.disabled = true;
        if (!squareCardContainer) {
            return;
        }
        if (!window.Square || !squareCardContainer.dataset.applicationId || !squareCardContainer.dataset.locationId) {
            setPaymentMessage('Square card entry could not load. Check the payment configuration and try again.', true);
            return;
        }

        try {
            const payments = window.Square.payments(
                squareCardContainer.dataset.applicationId,
                squareCardContainer.dataset.locationId
            );
            squareCard = await payments.card();
            await squareCard.attach('#square-card-container');
            checkoutSubmitButton.disabled = !hasItems;
        } catch {
            setPaymentMessage('Square card entry could not load. Refresh the page or contact the school for help.', true);
        }
    };

    void initializeSquareCard();
    checkoutForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!checkoutForm.reportValidity()) {
            return;
        }

        if (!squareCard || !totalDisplay) {
            setPaymentMessage('Secure card entry is not ready. Refresh the page and try again.', true);
            return;
        }

        checkoutSubmitButton.disabled = true;
        checkoutSubmitButton.textContent = 'Verifying card securely…';
        setPaymentMessage('');
        try {
            const tokenResult = await squareCard.tokenize({
                amount: Number(totalDisplay.textContent.replace(/[$,]/g, '')).toFixed(2),
                currencyCode: 'USD',
                intent: 'CHARGE',
                customerInitiated: true,
                sellerKeyedIn: false,
                billingContact: {
                    givenName: document.getElementById('billing-first-name').value.trim(),
                    familyName: document.getElementById('billing-last-name').value.trim(),
                    email: document.getElementById('billing-email').value.trim(),
                    phone: document.getElementById('billing-phone').value.trim(),
                    addressLines: [document.getElementById('billing-address').value.trim()],
                    city: document.getElementById('billing-city').value.trim(),
                    countryCode: 'US',
                    postalCode: document.getElementById('billing-zip').value.trim(),
                },
            });
            if (tokenResult.status !== 'OK' || !tokenResult.token) {
                throw new Error('Square could not verify these card details.');
            }

            document.getElementById('square-card-token').value = tokenResult.token;
            checkoutSubmitButton.textContent = 'Processing payment…';
            HTMLFormElement.prototype.submit.call(checkoutForm);
        } catch {
            checkoutSubmitButton.disabled = false;
            checkoutSubmitButton.textContent = 'Pay securely with Square';
            setPaymentMessage('We could not verify this card. Check the details or try another card.', true);
        }
    });
})();
