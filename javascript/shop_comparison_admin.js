(function () {
    'use strict';

    document.body.addEventListener('change', function (e) {
        var t = e.target;
        if (!(t instanceof Element)) {
            return;
        }

        if (!t.matches('.silvershop-on_feature_select_fetch_value_field')) {
            return;
        }

        var row = t.closest('tr');
        if (!row) {
            return;
        }

        var valueCell = row.querySelector('.col-Value');
        if (!valueCell) {
            return;
        }

        var sec = document.querySelector('input[name=SecurityID]');
        var securityId = sec instanceof HTMLInputElement ? sec.value : '';

        var inputInValue = valueCell.querySelector('input, select, textarea');
        var nameAttr = inputInValue instanceof HTMLElement ? inputInValue.getAttribute('name') : '';

        var params = new URLSearchParams({
            ID: String(t.value || ''),
            SecurityID: securityId,
            Name: nameAttr || ''
        });

        valueCell.innerHTML = '';

        fetch('ProductFeatureValueFieldController?' + params.toString(), {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                return response.text();
            })
            .then(function (html) {
                valueCell.innerHTML = html;
            })
            .catch(function () {});
    });
})();
