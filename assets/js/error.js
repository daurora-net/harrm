// -----------------------------
// 必須
// -----------------------------
function validateFields(fields) {
  let hasError = false;
  fields.forEach(function (field) {
    const inputEl = document.getElementById(field.inputId);
    const errorEl = document.getElementById(field.errorId);
    if (errorEl) {
      errorEl.innerHTML = "";
    }
    if (!inputEl || !inputEl.value.trim()) {
      if (errorEl) {
        errorEl.innerHTML = "⚠️ 必須";
      }
      if (!hasError && inputEl) {
        inputEl.focus();
      }
      hasError = true;
    }
  });
  return !hasError;
}

// レンタル追加
function validateRentalForm() {
  return validateFields([
    { inputId: 'addRentalTitle', errorId: 'rentalTitleErrorMessage' },
    { inputId: 'addRentalManager', errorId: 'rentalManagerErrorMessage' },
    { inputId: 'addRentalStart', errorId: 'rentalStartErrorMessage' },
    { inputId: 'addRentalEnd', errorId: 'rentalEndErrorMessage' }
  ]);
}

// レンタル編集
function validateEditEventForm() {
  return validateFields([
    { inputId: 'editEventTitle', errorId: 'editEventTitleErrorMessage' },
    { inputId: 'editEventManager', errorId: 'editEventManagerErrorMessage' },
    { inputId: 'editEventStart', errorId: 'editEventStartErrorMessage' },
    { inputId: 'editEventEnd', errorId: 'editEventEndErrorMessage' }
  ]);
}

// HDD追加
function validateAddHddForm() {
  let isValid = validateFields([
    { inputId: 'hddName', errorId: 'addHddNameErrorMessage' }
  ]);
  let hddNameEl = document.getElementById('hddName');
  if (hddNameEl && hddNameEl.value.trim().length > 10) {
    let errorEl = document.getElementById('addHddNameErrorMessage');
    if (errorEl) errorEl.innerHTML = "⚠️ 10文字以内";
    hddNameEl.focus();
    isValid = false;
  }
  return isValid;
}

// HDD編集
function validateEditHddForm() {
  let isValid = validateFields([
    { inputId: 'editHddName', errorId: 'editHddNameErrorMessage' }
  ]);
  let hddNameEl = document.getElementById('editHddName');
  if (hddNameEl && hddNameEl.value.trim().length > 10) {
    let errorEl = document.getElementById('editHddNameErrorMessage');
    if (errorEl) errorEl.innerHTML = "⚠️ 10文字以内";
    hddNameEl.focus();
    isValid = false;
  }
  return isValid;
}

// ユーザー編集
function validateEditUserForm() {
  return validateFields([
    { inputId: 'editUsername', errorId: 'editUsernameErrorMessage' }
  ]);
}

// -----------------------------
// 日付順序
// -----------------------------
const validateDateOrder = ({ startId, endId, returnId, endErrorId, returnErrorId, messages }) => {
  let isValid = true;
  const startInput = document.getElementById(startId);
  const endInput = document.getElementById(endId);
  const returnInput = returnId ? document.getElementById(returnId) : null;

  if (startInput && endInput && startInput.value && endInput.value) {
    const startDate = new Date(startInput.value);
    const endDate = new Date(endInput.value);
    if (startDate > endDate) {
      const endError = document.getElementById(endErrorId);
      if (endError) endError.innerHTML = messages.end;
      isValid = false;
    }
  }

  if (returnInput && startInput && startInput.value && returnInput.value) {
    const startDate = new Date(startInput.value);
    const returnDate = new Date(returnInput.value);
    if (returnDate <= startDate) {
      const returnError = document.getElementById(returnErrorId);
      if (returnError) returnError.innerHTML = messages.return;
      isValid = false;
    }
  }
  return isValid;
};

// レンタル追加
const validateRentalDateOrder = () =>
  validateDateOrder({
    startId: 'addRentalStart',
    endId: 'addRentalEnd',
    returnId: 'addReturnDate',
    endErrorId: 'rentalEndErrorMessage',
    returnErrorId: 'addReturnErrorMessage',
    messages: {
      end: "⚠️ 開始日より後",
      return: "⚠️ 開始日より後"
    }
  });

// レンタル編集
const validateEditEventDateOrder = () =>
  validateDateOrder({
    startId: 'editEventStart',
    endId: 'editEventEnd',
    returnId: 'editReturnDate',
    endErrorId: 'editEventEndErrorMessage',
    returnErrorId: 'editReturnErrorMessage',
    messages: {
      end: "⚠️ 開始日より後",
      return: "⚠️ 開始日より後"
    }
  });