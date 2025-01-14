learndash.forms=learndash.forms||{},learndash.forms.validation=learndash.forms.validation||{},((e,r,t)=>{t.initRequiredFields=e=>{e.addEventListener("blur",(r=>{r.target.matches("input[required]")&&(t.isFieldFilled(r.target)||t.addError(r.target,t.i18n.requiredErrorMessage),t.setSubmitDisableState(e))}),!0),e.addEventListener("focus",(r=>{r.target.matches("input[required]")&&(t.removeError(r.target),t.setSubmitDisableState(e))}),!0),e.addEventListener("keyup",(r=>{r.target.matches("input[required]")&&t.setSubmitDisableState(e)}),!0),r.addEventListener("learndashPasswordFieldChange",(()=>{t.setSubmitDisableState(e)}),!0)},t.setSubmitDisableState=e=>{const r=e.querySelector('[type="submit"]');r&&(t.isFormFilled(e)&&t.isFormErrorFree(e)?r.removeAttribute("disabled"):r.setAttribute("disabled","disabled"))},t.isFormFilled=e=>{const r=e.querySelectorAll("input[required]");let i=!0;return r.forEach((e=>{t.isFieldFilled(e)||(i=!1)})),i},t.isFormErrorFree=e=>!e.querySelectorAll(".ld-form__field--error, .ld-password-strength.bad").length,t.isFieldFilled=e=>""!==e.value.trim(),t.addError=(e,t)=>{e.classList.add("ld-form__field--error"),e.classList.remove("ld-form__field--valid");const i=e.closest(".ld-form__field-wrapper");if(!i)return;if(i.nextElementSibling&&i.nextElementSibling.classList.contains("ld-form__field-error-message"))return;const l=r.createElement("span");l.className="ld-form__field-error-message",l.textContent=t,l.setAttribute("aria-live","polite"),i.insertAdjacentElement("afterend",l)},t.removeError=e=>{e.classList.remove("ld-form__field--error");const r=e.closest(".ld-form__field-wrapper");r&&r.nextElementSibling&&r.nextElementSibling.classList.contains("ld-form__field-error-message")&&r.nextElementSibling.remove()},t.initValidation=()=>{r.querySelectorAll('[data-learndash-validate="true"]').forEach((e=>{t.initRequiredFields(e),t.setSubmitDisableState(e)}))},r.addEventListener("DOMContentLoaded",(()=>{t.initValidation()}))})(window,document,learndash.forms.validation);