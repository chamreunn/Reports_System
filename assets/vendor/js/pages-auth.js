'use strict';
const formAuthentication = document.querySelector('#formAuthentication');
document.addEventListener('DOMContentLoaded', function (e) {
  var t;
  formAuthentication &&
    FormValidation.formValidation(formAuthentication, {
      fields: {
        modalPermissionName: {
          validators: {
            notEmpty: { message: 'Please enter username' },
            stringLength: { min: 6, message: 'Username must be more than 6 characters' }
          }
        },
        email: {
          validators: {
            notEmpty: { message: 'សូមបញ្ចូលអាសយដ្ឋានអ៊ីមែល' },
            emailAddress: { message: 'សូមបញ្ចូលអាសយដ្ឋានអ៊ីមែលដែលត្រឹមត្រូវ' }
          }
        },
        usercontact: {
          validators: {
            notEmpty: { message: 'សូមបញ្ចូលលេខទូរស័ព្ទ' },
          }
        },
        username: {
          validators: {
            notEmpty: { message: 'សូមវាយបញ្ចូលឈ្មោះមន្ត្រី' },
            stringLength: { min: 6, message: 'ឈ្មោះមន្ត្រីត្រូវលើសពី៦អក្សរ' }
          }
        },
        modalPermissionName: {
          validators: {
            notEmpty: { message: 'សូមវាយបញ្ចូលឈ្មោះលក្ខខណ្ឌ' },
            stringLength: { min: 6, message: 'ឈ្មោះមន្ត្រីត្រូវលើសពី៦អក្សរ' }
          }
        },
        password: {
          validators: {
            notEmpty: { message: 'សូមវាយបញ្ចូលពាក្យសម្ងាត់' },
            stringLength: { min: 8, message: 'ពាក្យសម្ងាត់ត្រូវចាប់ពី៨ខ្ទង់' }
          }
        },
        newpassword: {
          validators: {
            notEmpty: { message: 'សូមវាយបញ្ចូលពាក្យសម្ងាត់ថ្មី' },
            stringLength: { min: 8, message: 'ពាក្យសម្ងាត់ថ្មីត្រូវចាប់ពី៨ខ្ទង់' }
          }
        },
        'confirm-password': {
          validators: {
            notEmpty: { message: 'សូមវាយបញ្ចូលបញ្ជាក់ពាក្យសម្ងាត់ថ្មី' },
            identical: {
              compare: function () {
                return formAuthentication.querySelector('[name="newpassword"]').value;
              },
              message: 'ពាក្យសម្ងាត់ថ្មី និងបញ្ជាក់ពាក្យសម្ងាត់ថ្មីមិនដូចគ្នា'
            },
            stringLength: { min: 6, message: 'ពាក្យសម្ងាត់ថ្មីត្រូវចាប់ពី៨ខ្ទង់' }
          }
        },
        formValidationCheckbox: {
          validators: {
            notEmpty: {
              message: 'សូមធីកទៅលើប្រអប់ខាងលើជាមុនសិន'
            }
          }
        },
        pname: { validators: { notEmpty: { message: 'សូមបញ្ចូលមុខតំណែង' } } },
        lname: { validators: { notEmpty: { message: 'សូមបញ្ចូលឈ្មោះប្រភេទច្បាប់' } } },
        ldescribe: { validators: { notEmpty: { message: 'សូមពណ៌នាអំពីប្រភេទច្បាប់ដែលបានបង្កើត' } } },
        latename: { validators: { notEmpty: { message: 'សូមបញ្ចូលឈ្មោះនៃប្រភេទយឺត' } } },
        inlineRadioOptions: { validators: { notEmpty: { message: 'សូមជ្រើសរើសភេទ' } } },
        role: { validators: { notEmpty: { message: 'សូមជ្រើសរើសRole' } } },
        userfullname: { validators: { notEmpty: { message: 'សូមបញ្ចូលឈ្មោះមន្ត្រី' } } },
        latenote: { validators: { notEmpty: { message: 'សូមបញ្ចូលការចំណាំទៅលើប្រភេទយឺត' } } },
        address: { validators: { notEmpty: { message: 'សូមបញ្ចូលអាសយដ្ឋាន' } } },
        terms: { validators: { notEmpty: { message: 'Please agree terms & conditions' } } },
        eusername: { validators: { notEmpty: { message: 'សូមបញ្ចូលគោត្តនាម និងនាម' } } },
        euserid: { validators: { notEmpty: { message: 'សូមបញ្ចូលលេខកូដមន្ត្រី' } } },
        eemail: { validators: { notEmpty: { message: 'សូមបញ្ចូលអាសយដ្ឋានអ៊ីមែល' } } },
        dob: { validators: { notEmpty: { message: 'សូមជ្រើសរើសថ្ងៃខែឆ្នាំកំណើត' } } },
        department: { validators: { notEmpty: { message: 'សូមជ្រើសរើសនាយកដ្ឋាន' } } },
        eoffice: { validators: { notEmpty: { message: 'សូមជ្រើសរើសការិយាល័យ' } } },
        eposition: { validators: { notEmpty: { message: 'សូមជ្រើសរើសតួនាទី' } } },
        eaddress: { validators: { notEmpty: { message: 'សូមជ្រើសរើសតួនាទី' } } },
        gender: { validators: { notEmpty: { message: 'សូមជ្រើសរើសភេទ' } } },
        contact: { validators: { notEmpty: { message: 'សូមបញ្ចូលលេខទូរស័ព្ទ' } } },
        firstname: { validators: { notEmpty: { message: 'សូមបញ្ចូលគោត្តនាម' } } },
        lastname: { validators: { notEmpty: { message: 'សូមបញ្ចូលនាម' } } },
        engnameper: { validators: { notEmpty: { message: 'Please Fill Name' } } },
        pertype: { validators: { notEmpty: { message: 'សូមជ្រើសរើសប្រភេទ' } } },
        fromdate: { validators: { notEmpty: { message: 'សូមជ្រើសរើសចាប់ពីថ្ងៃខែឆ្នាំ' } } },
        todate: { validators: { notEmpty: { message: 'សូមជ្រើសរើសដល់ថ្ងៃទីខែឆ្នាំ' } } },
        reason: { validators: { notEmpty: { message: 'សូមជ្រើសរើសវាយបញ្ចូលមូលហេតុនៃការស្នើរសុំច្បាប់ឈប់សម្រាក' } } },
        leavetypes: { validators: { notEmpty: { message: 'សូមជ្រើសរើសប្រភេទច្បាប់ឈប់សម្រាក' } } },
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: '', rowSelector: '.mb-3' }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      },
      init: e => {
        e.on('plugins.message.placed', function (e) {
          e.element.parentElement.classList.contains('input-group') &&
            e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
        });
      }
    }),
    (t = document.querySelectorAll('.numeral-mask')).length &&
      t.forEach(e => {
        new Cleave(e, { numeral: !0 });
      });
});
'use strict';
!(function () {
  window.Helpers.initCustomOptionCheck();
  const e = [].slice.call(document.querySelectorAll('.flatpickr-validation'));
  e &&
    e.forEach(e => {
      e.flatpickr({
        allowInput: !0,
        monthSelectorType: 'static'
      });
    });
  const a = document.querySelectorAll('.needs-validation');
  Array.prototype.slice.call(a).forEach(function (e) {
    e.addEventListener(
      'submit',
      function (a) {
        e.checkValidity() ? alert('Submitted!!!') : (a.preventDefault(), a.stopPropagation()),
          e.classList.add('was-validated');
      },
      !1
    );
  });
})(),
  document.addEventListener('DOMContentLoaded', function (e) {
    !(function () {
      const e = document.getElementById('formValidationExamples'),
        a = jQuery(e.querySelector('[name="formValidationSelect2"]')),
        t = jQuery(e.querySelector('[name="formValidationTech"]')),
        o = e.querySelector('[name="formValidationLang"]'),
        n = jQuery(e.querySelector('.selectpicker')),
        i = FormValidation.formValidation(e, {
          fields: {
            formValidationName: {
              validators: {
                notEmpty: {
                  message: 'Please enter your name'
                },
                stringLength: {
                  min: 6,
                  max: 30,
                  message: 'The name must be more than 6 and less than 30 characters long'
                },
                regexp: {
                  regexp: /^[a-zA-Z0-9 ]+$/,
                  message: 'The name can only consist of alphabetical, number and space'
                }
              }
            },
            formValidationEmail: {
              validators: {
                notEmpty: {
                  message: 'Please enter your email'
                },
                emailAddress: {
                  message: 'The value is not a valid email address'
                }
              }
            },
            formValidationPass: {
              validators: {
                notEmpty: {
                  message: 'Please enter your password'
                }
              }
            },
            formValidationConfirmPass: {
              validators: {
                notEmpty: {
                  message: 'Please confirm password'
                },
                identical: {
                  compare: function () {
                    return e.querySelector('[name="formValidationPass"]').value;
                  },
                  message: 'The password and its confirm are not the same'
                }
              }
            },
            formValidationFile: {
              validators: {
                notEmpty: {
                  message: 'Please select the file'
                }
              }
            },
            formValidationDob: {
              validators: {
                notEmpty: {
                  message: 'Please select your DOB'
                },
                date: {
                  format: 'YYYY/MM/DD',
                  message: 'The value is not a valid date'
                }
              }
            },
            formValidationSelect2: {
              validators: {
                notEmpty: {
                  message: 'Please select your country'
                }
              }
            },
            formValidationLang: {
              validators: {
                notEmpty: {
                  message: 'Please add your language'
                }
              }
            },
            formValidationTech: {
              validators: {
                notEmpty: {
                  message: 'Please select technology'
                }
              }
            },
            formValidationBio: {
              validators: {
                notEmpty: {
                  message: 'Please enter your bio'
                },
                stringLength: {
                  min: 100,
                  max: 500,
                  message: 'The bio must be more than 100 and less than 500 characters long'
                }
              }
            },
            hoffice: {
              validators: {
                notEmpty: {
                  message: 'Please select your gender'
                }
              }
            },
            formValidationPlan: {
              validators: {
                notEmpty: {
                  message: 'Please select your preferred plan'
                }
              }
            },
            formValidationSwitch: {
              validators: {
                notEmpty: {
                  message: 'Please select your preference'
                }
              }
            },
            formValidationCheckbox: {
              validators: {
                notEmpty: {
                  message: 'Please confirm our T&C'
                }
              }
            }
          },
          plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
              eleValidClass: '',
              rowSelector: function (e, a) {
                switch (e) {
                  case 'formValidationName':
                  case 'formValidationEmail':
                  case 'formValidationPass':
                  case 'formValidationConfirmPass':
                  case 'formValidationFile':
                  case 'formValidationDob':
                  case 'formValidationSelect2':
                  case 'formValidationLang':
                  case 'formValidationTech':
                  case 'formValidationHobbies':
                  case 'formValidationBio':
                  case 'formValidationGender':
                    return '.col-md-6';
                  case 'formValidationPlan':
                    return '.col-xl-3';
                  case 'formValidationSwitch':
                  case 'formValidationCheckbox':
                    return '.col-12';
                  default:
                    return '.row';
                }
              }
            }),
            submitButton: new FormValidation.plugins.SubmitButton(),
            defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
            autoFocus: new FormValidation.plugins.AutoFocus()
          },
          init: e => {
            e.on('plugins.message.placed', function (e) {
              e.element.parentElement.classList.contains('input-group') &&
                e.element.parentElement.insertAdjacentElement('afterend', e.messageElement),
                e.element.parentElement.parentElement.classList.contains('custom-option') &&
                  e.element.closest('.row').insertAdjacentElement('afterend', e.messageElement);
            });
          }
        });
      flatpickr(e.querySelector('[name="formValidationDob"]'), {
        enableTime: !1,
        dateFormat: 'Y/m/d',
        onChange: function () {
          i.revalidateField('formValidationDob');
        }
      }),
        a.length &&
          (a.wrap('<div class="position-relative"></div>'),
          a
            .select2({
              placeholder: 'Select country',
              dropdownParent: a.parent()
            })
            .on('change.select2', function () {
              i.revalidateField('formValidationSelect2');
            }));
      if (isRtl) {
        const e = [].slice.call(document.querySelectorAll('.typeahead'));
        e &&
          e.forEach(e => {
            e.setAttribute('dir', 'rtl');
          });
      }
      var l;
      t.typeahead(
        {
          hint: !isRtl,
          highlight: !0,
          minLength: 1
        },
        {
          name: 'tech',
          source:
            ((l = [
              'ReactJS',
              'Angular',
              'VueJS',
              'Html',
              'Css',
              'Sass',
              'Pug',
              'Gulp',
              'Php',
              'Laravel',
              'Python',
              'Bootstrap',
              'Material Design',
              'NodeJS'
            ]),
            function (e, a) {
              var t, o;
              (t = []),
                (o = new RegExp(e, 'i')),
                $.each(l, function (e, a) {
                  o.test(a) && t.push(a);
                }),
                a(t);
            })
        }
      );
      new Tagify(o);
      o.addEventListener('change', function () {
        i.revalidateField('formValidationLang');
      }),
        n.on('changed.bs.select', function (e, a, t, o) {
          i.revalidateField('formValidationHobbies');
        });
    })();
  });
