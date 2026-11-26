/* =============================================================
   Client-side validation & interactivity
   -------------------------------------------------------------
   - Validates Login, Sign-up and Student forms before submit
   - Live error messages while typing
   - Password show/hide + strength meter
   - Delete confirmation, dismissible alerts, mobile menu
   - XML page: tabs, table filter, syntax highlighting, copy
   ============================================================= */

(function () {
    'use strict';

    /* ---------- Validation rules ---------- */
    var patterns = {
        name:     /^[A-Za-z][A-Za-z .'-]{1,99}$/,
        username: /^[A-Za-z0-9_]{4,30}$/,
        email:    /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,
        regNo:    /^[A-Z0-9]{5,20}$/,
        mobile:   /^[6-9][0-9]{9}$/
    };

    // Each rule returns an error message, or '' when the value is valid.
    var rules = {
        login: {
            username: function (v) { return v.trim() === '' ? 'Please enter your username or email.' : ''; },
            password: function (v) { return v === '' ? 'Please enter your password.' : ''; }
        },
        register: {
            full_name: function (v) {
                return patterns.name.test(v.trim()) ? '' : 'Enter your full name (letters and spaces only).';
            },
            username: function (v) {
                return patterns.username.test(v.trim()) ? '' : 'Username must be 4-30 characters: letters, digits or underscore.';
            },
            email: function (v) {
                return patterns.email.test(v.trim()) ? '' : 'Enter a valid email address.';
            },
            password: function (v) {
                if (v.length < 6) return 'Password must be at least 6 characters.';
                if (!/[A-Za-z]/.test(v) || !/\d/.test(v)) return 'Password must contain a letter and a number.';
                return '';
            },
            confirm_password: function (v, form) {
                if (v === '') return 'Please confirm your password.';
                return v === form.password.value ? '' : 'Passwords do not match.';
            }
        },
        student: {
            reg_no: function (v) {
                v = v.trim();
                if (v === '') return 'Register number is required.';
                return patterns.regNo.test(v) ? '' : 'Use 5-20 capital letters and digits only (e.g. U03BC24S0001).';
            },
            name: function (v) {
                if (v.trim() === '') return 'Student name is required.';
                return patterns.name.test(v.trim()) ? '' : 'Name may contain only letters, spaces, dots or hyphens.';
            },
            course: function (v) { return v === '' ? 'Please select a course.' : ''; },
            semester: function (v) { return v === '' ? 'Please select a semester.' : ''; },
            email: function (v) {
                if (v.trim() === '') return 'Email ID is required.';
                return patterns.email.test(v.trim()) ? '' : 'Enter a valid email address (e.g. name@example.com).';
            },
            contact_number: function (v) {
                if (v.trim() === '') return 'Contact number is required.';
                return patterns.mobile.test(v.trim()) ? '' : 'Enter a valid 10-digit mobile number starting with 6-9.';
            }
        }
    };

    function showError(field, message) {
        var group = field.closest('.form-group');
        if (!group) return;
        var box = group.querySelector('.error-text');
        if (box) box.textContent = message;
        group.classList.toggle('has-error', message !== '');
        group.classList.toggle('is-valid', message === '' && field.value !== '');
    }

    function validateField(form, formRules, name) {
        var field = form.elements[name];
        if (!field) return true;
        var message = formRules[name](field.value, form);
        showError(field, message);
        return message === '';
    }

    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        var formRules = rules[form.getAttribute('data-validate')];
        if (!formRules) return;

        // Validate the whole form when it is submitted
        form.addEventListener('submit', function (event) {
            var firstInvalid = null;
            Object.keys(formRules).forEach(function (name) {
                if (!validateField(form, formRules, name) && !firstInvalid) {
                    firstInvalid = form.elements[name];
                }
            });
            if (firstInvalid) {
                event.preventDefault();
                firstInvalid.focus();
            }
        });

        // Live validation once the user leaves a field / changes it
        Object.keys(formRules).forEach(function (name) {
            var field = form.elements[name];
            if (!field) return;
            var touched = false;
            field.addEventListener('blur', function () { touched = true; validateField(form, formRules, name); });
            field.addEventListener('change', function () { touched = true; validateField(form, formRules, name); });
            field.addEventListener('input', function () {
                if (touched) validateField(form, formRules, name);
                if (name === 'password' && form.elements.confirm_password && form.elements.confirm_password.value) {
                    validateField(form, formRules, 'confirm_password');
                }
            });
        });

        // Clear messages when the form is reset
        form.addEventListener('reset', function () {
            setTimeout(function () {
                form.querySelectorAll('.form-group').forEach(function (g) {
                    g.classList.remove('has-error', 'is-valid');
                    var box = g.querySelector('.error-text');
                    if (box) box.textContent = '';
                });
            }, 0);
        });
    });

    /* ---------- Input helpers ---------- */
    var regNo = document.getElementById('reg_no');
    if (regNo) {
        regNo.addEventListener('input', function () {
            var pos = regNo.selectionStart;
            regNo.value = regNo.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            regNo.setSelectionRange(pos, pos);
        });
    }
    var contact = document.getElementById('contact_number');
    if (contact) {
        contact.addEventListener('input', function () {
            contact.value = contact.value.replace(/\D/g, '').slice(0, 10);
        });
    }

    /* ---------- Show / hide password ---------- */
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.getAttribute('data-target'));
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.textContent = show ? 'Hide' : 'Show';
        });
    });

    /* ---------- Password strength meter (sign-up) ---------- */
    var strength = document.getElementById('strength');
    var regPassword = document.querySelector('#registerForm #password');
    if (strength && regPassword) {
        var bar = strength.querySelector('span');
        regPassword.addEventListener('input', function () {
            var v = regPassword.value, score = 0;
            if (v.length >= 6) score++;
            if (v.length >= 10) score++;
            if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
            if (/\d/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;
            var colors = ['#dc2626', '#dc2626', '#f59e0b', '#eab308', '#22c55e', '#16a34a'];
            bar.style.width = (v ? Math.max(score, 1) * 20 : 0) + '%';
            bar.style.background = colors[score];
        });
    }

    /* ---------- Confirm before deleting ---------- */
    document.querySelectorAll('.delete-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var msg = 'Are you sure you want to delete the record of\n\n' +
                      form.getAttribute('data-name') + ' (' + form.getAttribute('data-reg') + ')?\n\n' +
                      'This action cannot be undone.';
            if (!window.confirm(msg)) event.preventDefault();
        });
    });

    /* ---------- Alerts: close button + auto hide ---------- */
    document.querySelectorAll('.alert-close').forEach(function (btn) {
        btn.addEventListener('click', function () { btn.parentElement.remove(); });
    });
    document.querySelectorAll('.alert[data-autohide]').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity .4s';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 400);
        }, 5000);
    });

    /* ---------- Mobile navigation ---------- */
    var toggle = document.getElementById('navToggle');
    if (toggle) {
        toggle.addEventListener('click', function () {
            document.getElementById('navLinks').classList.toggle('open');
        });
    }

    /* ---------- Animate dashboard bars ---------- */
    document.querySelectorAll('.bar-fill').forEach(function (bar) {
        var w = bar.style.width;
        bar.style.width = '0';
        requestAnimationFrame(function () { setTimeout(function () { bar.style.width = w; }, 50); });
    });

    /* ---------- XML page: tabs ---------- */
    document.querySelectorAll('[data-tabs] .tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('[data-tabs] .tab').forEach(function (t) { t.classList.remove('active'); });
            document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.remove('active'); });
            tab.classList.add('active');
            document.getElementById(tab.getAttribute('data-tab')).classList.add('active');
        });
    });

    /* ---------- XML page: filter table rows ---------- */
    var xmlSearch = document.getElementById('xmlSearch');
    if (xmlSearch) {
        xmlSearch.addEventListener('input', function () {
            var q = xmlSearch.value.toLowerCase();
            document.querySelectorAll('#xmlTable tbody tr').forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none';
            });
        });
    }

    /* ---------- XML page: syntax highlight + copy ---------- */
    var src = document.getElementById('xmlSource');
    if (src) {
        var raw = src.textContent;
        var html = raw
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/(&lt;\?.*?\?&gt;|&lt;!DOCTYPE.*?&gt;)/g, '<span class="x-pi">$1</span>')
            .replace(/(&lt;\/?)([\w]+)((?:\s+[\w-]+="[^"]*")*)\s*(\/?&gt;)/g, function (m, open, tag, attrs, close) {
                attrs = attrs.replace(/([\w-]+)=("[^"]*")/g, '<span class="x-attr">$1</span>=<span class="x-val">$2</span>');
                return '<span class="x-tag">' + open + tag + '</span>' + attrs + '<span class="x-tag">' + close + '</span>';
            });
        src.innerHTML = html;

        var copy = document.getElementById('copyXml');
        if (copy) {
            copy.addEventListener('click', function () {
                navigator.clipboard.writeText(raw).then(function () {
                    copy.textContent = 'Copied!';
                    setTimeout(function () { copy.textContent = 'Copy'; }, 1500);
                });
            });
        }
    }
})();
