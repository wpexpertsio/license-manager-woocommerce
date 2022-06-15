document.addEventListener('DOMContentLoaded', function(event) {

    var licenseTable = {
        btnHideLicense: document.querySelectorAll('.lmfwc-license-key-hide'),
        btnShowLicense: document.querySelectorAll('.lmfwc-license-key-show'),
        btnCopyToClipboard: document.querySelectorAll('code.lmfwc-placeholder'),
        txtCopiedToClipboard: document.querySelector('.lmfwc-txt-copied-to-clipboard'),
        bindEventListeners: function() {
            let i;
            const that = this;

            if (this.btnHideLicense) {
                for (i = 0; i < this.btnHideLicense.length; i++) {
                    this.btnHideLicense[i].addEventListener('click', function() {
                        that.hideLicenseKey(this);
                    });
                }
            }

            if (this.btnShowLicense) {
                for (i = 0; i < this.btnShowLicense.length; i++) {
                    this.btnShowLicense[i].addEventListener('click', function() {
                        that.showLicenseKey(this);
                    });
                }
            }

            if (this.btnCopyToClipboard) {
                for (i = 0; i < this.btnCopyToClipboard.length; i++) {
                    this.btnCopyToClipboard[i].addEventListener('click', function(e) {
                        that.copyToClipboard(this, e);
                    });
                }
            }
        },
        hideLicenseKey: function(el) {
            const code = el.parentNode.parentNode.previousSibling.previousSibling;

            code.innerText = '';
            code.classList.add('empty');
        },
        showLicenseKey: function(el) {
            const licenseKeyId = parseInt(el.dataset.id);
            const spinner      = el.parentNode.parentNode.previousSibling;
            const code         = spinner.previousSibling;

            spinner.style.opacity = 1;

            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'lmfwc_show_license_key',
                    show: license.show,
                    id: licenseKeyId
                },
                success: function(response) {
                    code.classList.remove('empty');
                    code.innerText = response;
                },
                error: function(response) {
                    console.log(response);
                },
                complete: function() {
                    spinner.style.opacity = 0;
                }
            });
        },
        copyToClipboard: function(el, e) {
            // Copy to clipboard
            const str = el.innerText.toString();

            if (!str) return;

            const textArea = document.createElement('textarea');
            textArea.value = str;
            textArea.setAttribute('readonly', '');
            textArea.style.position = 'absolute';
            textArea.style.left = '-9999px';
            document.body.appendChild(textArea);
            const selected =
                document.getSelection().rangeCount > 0
                    ? document.getSelection().getRangeAt(0)
                    : false;
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            if (selected) {
                document.getSelection().removeAllRanges();
                document.getSelection().addRange(selected);
            }

            // Display info
            const copied = document.createElement('div');
            copied.classList.add('lmfwc-clipboard');
            copied.style.position = 'absolute';
            copied.style.left = e.clientX.toString() + 'px';
            copied.style.top = (window.pageYOffset + e.clientY).toString() + 'px';
            copied.innerText = document.querySelector('.lmfwc-txt-copied-to-clipboard').innerText.toString();
            document.body.appendChild(copied);

            setTimeout(function() {
                copied.style.opacity = '0';
            }, 700);
            setTimeout(function() {
                document.body.removeChild(copied);
            }, 1500);
        },
        init: function() {
            this.bindEventListeners();
        }
    };

    var orderLicenses = {
        btnShow: document.querySelectorAll('.lmfwc-license-keys-show-all'),
        btnHide: document.querySelectorAll('.lmfwc-license-keys-hide-all'),
        getLicenseKeyIds: function(button) {
            const licenseKeyIds = [];
            const codeList = button.parentNode.previousSibling.children;

            for (let i = 0; i < codeList.length; i++) {
                licenseKeyIds.push(parseInt(codeList[i].children[0].dataset.id));
            }

            return licenseKeyIds;
        },
        bindShow: function() {
            if (!this.btnShow) return;

            const licenseTableObject = this;

            for (let i = 0; i < this.btnShow.length; i++) {
                const btnShow = this.btnShow[i];

                btnShow.addEventListener('click', function() {
                    const currentButton = this;
                    const spinner       = currentButton.nextSibling.nextSibling;
                    const licenseKeyIds = licenseTableObject.getLicenseKeyIds(currentButton);
                    spinner.style.opacity = 1;

                    jQuery.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'lmfwc_show_all_license_keys',
                            show_all: license.show_all,
                            ids: JSON.stringify(licenseKeyIds)
                        },
                        success: function(response) {
                            const licenseKeys = response;

                            for (const id in licenseKeys) {
                                if (!licenseKeys.hasOwnProperty(id)) {
                                    continue;
                                }

                                const licenseKey = document.querySelector('.lmfwc-placeholder[data-id="' + id + '"]');
                                licenseKey.classList.remove('empty');
                                licenseKey.innerText = licenseKeys[id];
                            }
                        },
                        error: function(response) {
                            console.log(response);
                        },
                        complete: function() {
                            spinner.style.opacity = 0;
                        }
                    });
                });
            }
        },
        bindHide: function() {
            if (!this.btnHide) return;

            const licenseTableObject = this;

            for (let i = 0; i < this.btnHide.length; i++) {
                const btnHide = this.btnHide[i];

                btnHide.addEventListener('click', function() {
                    const currentButton = this;
                    const licenseKeyIds = licenseTableObject.getLicenseKeyIds(currentButton);

                    for (const id in licenseKeyIds) {
                        if (!licenseKeyIds.hasOwnProperty(id)) {
                            continue;
                        }

                        const licenseKey = document.querySelector('.lmfwc-placeholder[data-id="' + licenseKeyIds[id] + '"]');
                        licenseKey.classList.add('empty');
                        licenseKey.innerText = '';
                    }
                });
            }
        },
        init: function() {
            this.bindShow();
            this.bindHide();
        }
    };

    licenseTable.init();
    orderLicenses.init();
});