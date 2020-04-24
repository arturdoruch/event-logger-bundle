/*!
 * (c) 2017 Artur Doruch <arturdoruch@interia.pl>
 */

define([
    'arturdoruchJs/util/dateUtils',
    'arturdoruchJsVendor/jquery/ui/jquery-ui.min'
], function () {
    /**
     * @param {string} fieldNameFrom
     * @param {string} filedNameTo
     * @param {string} format Date format
     */
    var Class = function (fieldNameFrom, filedNameTo, format) {
        this.$dateFrom = $('input[name="' + fieldNameFrom + '"]');
        this.$dateTo = $('input[name="' + filedNameTo + '"]');
        this.dateFormat = format
            .replace(/MM/, 'mm')
            .replace(/yyyy/, 'yy');

        this.setDatePicker();
    };

    Class.prototype = {
        setDatePicker: function() {
            var self = this;

            self.$dateFrom.datepicker({

                dateFormat: self.dateFormat,
                maxDate: createDate(),
                onSelect: function(date) {
                    // Set min date for dateTo input field.
                    self.$dateTo.datepicker("option", "minDate", date);
                },
                onClose: function () {
                    validateDate(self.$dateFrom);
                }
            });

            self.$dateTo.datepicker({
                dateFormat: self.dateFormat,
                maxDate: createDate(1),
                onSelect: function(date) {
                    // Set max date for dateFrom input field.
                    self.$dateFrom.datepicker("option", "maxDate", date);
                },
                onClose: function () {
                    validateDate(self.$dateTo);
                }
            });
        },

        /**
         * @param {boolean} currentState
         */
        toggleDisabled: function (currentState) {
            this.$dateFrom.attr('disabled', !currentState);
            this.$dateTo.attr('disabled', !currentState);
        }
    };

    function validateDate($date) {
        var date = $date.val();

        if (date && !isValidDate(date)) {
            $date.closest('div.form-group').addClass('has-error');
            // todo Add error message.

            return;
        }

        $date.closest('div.form-group').removeClass('has-error');
    }

    /**
     * @param {string} date The date in ISO format YYYY-MM-DD
     * @returns {boolean}
     */
    function isValidDate(date) {
        if (!/^\d{2}.\d{2}.\d{4}$/.test(date)) {
            return false;
        }

        var bits = date.split('.'),
            day = bits[0],
            month = bits[1] - 1,
            year = bits[2],
            _date = new Date(year, month, day);

        return _date.getFullYear() == year && _date.getMonth() == month && _date.getDate() == Number(day);
    }

    function createDate(days) {
        var date = new Date().moveDays(days);

        return date.getDate() + '.' + (date.getMonth() + 1) + '.' + date.getFullYear();
    }

    return Class;
});