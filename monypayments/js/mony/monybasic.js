/**
 * Mony payment basic javascript for Magento
 *
 * @package   Mony_Monypayments
 * @author    Mony payments <support@monypayments.com>
 * @copyright Copyright (c) 2015-2016 Mony payments (http://www.monypayments.com)
 */
var MonyBasic = Class.create();

MonyBasic.prototype = {
    /**
     * initialize function when class created
     */
    initialize: function()
    {
        // nothing for now
    },

    /**
     * Create hidden input value for supporting onestep checkout
     *
     * @array Must be an array of key = input name; value = input value
     */
    createHiddenInput: function(array, appendId)
    {
        // Setting up standard variable
        var input, attr, value;
        var regExp = /\[(.*?)\]/;
        // Looping and create hidden input based on array given
        for (attr in array) {

            // Check whether key is no null
            if (array.hasOwnProperty(attr)) {
                
                var id = regExp.exec(attr)[1];
		
                // Start creating input
                input = document.createElement('input');
                input.setAttribute('type', 'hidden');
                input.setAttribute('id', id);
                input.setAttribute('name', attr);
                input.setAttribute('value', array[attr]);

                // Delete if already exist
                var element = document.getElementById(id);
                if (element) {
                    element.parentNode.removeChild(element);
                }
                // Adding input to a form
                $(appendId).appendChild(input);
            }
        }
    },

    /**
     * enabling Mony payment after disabling through JS
     */
    enableMonyPayment: function()
    {
        $('payment_form_monypayments').style.display = "block";
        $('monypayments_inline_error').style.display = "none";
    },

    /**
     * Disabling Mony payment function
     */
    disableMonyPayment: function()
    {
        $('payment_form_monypayments').style.display = "none";
        $('monypayments_inline_error').style.display = "block";
    },

    /**
     * Remove class function for vanilla JS
     *
     * @param element
     * @param classRemove
     */
    removeClass: function(element, classRemove)
    {
        if (typeof element !== 'object') {
            element = document.getElementById(element);
        }

        if (element.classList)
            element.classList.remove(classRemove);
        else
            element.classRemove = element.classRemove.replace(new RegExp('(^|\\b)' + classRemove.split(' ').join('|') + '(\\b|$)', 'gi'), ' ');
    },

    /**
     * Add class function for vanila JS
     *
     * @param element
     * @param classAdd
     */
    addClass: function(element, classAdd)
    {
        if (typeof element !== 'object') {
            element = document.getElementById(element);
        }

        if (element.classList)
            element.classList.add(classAdd);
        else
            element.classAdd += ' ' + classAdd;
    },

    /**
     *
     * Find closest parent node
     *
     * @param elem
     * @param selector
     * @returns {*}
     */
    findClosest: function (elem, selector)
    {
        var firstChar = selector.charAt(0);
        // Get closest match
        for ( ; elem && elem !== document; elem = elem.parentNode ) {
            // If selector is a class
            if ( firstChar === '.' ) {
                if ( elem.classList.contains( selector.substr(1) ) ) {
                    return elem;
                }
            }
            // If selector is an ID
            if ( firstChar === '#' ) {
                if ( elem.id === selector.substr(1) ) {
                    return elem;
                }
            }
            // If selector is a data attribute
            if ( firstChar === '[' ) {
                if ( elem.hasAttribute( selector.substr(1, selector.length - 2) ) ) {
                    return elem;
                }
            }
            // If selector is a tag
            if ( elem.tagName.toLowerCase() === selector ) {
                return elem;
            }

        }

        return false;
    },

    newPayment: function(isNew)
    {
        if (isNew) {
            Element.show('monypayments-new-payment-form');
        } else {
            Element.hide('monypayments-new-payment-form');
        }
    }
};