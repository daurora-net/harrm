<div id="editEventModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="document.getElementById('editEventModal').style.display='none'">
      <i class="fa-solid fa-xmark"></i>
    </span>
    <form id="editEventForm" method="post" class="form">
      <h3>スケジュール編集</h3>
      <div class="error-message-wrap">
        <div id="editEventErrorMessage" class="error-message"></div>
      </div>
      <input type="hidden" id="editEventId" name="eventId">

      <div class="flex">
        <div class="form-content w-300px">
          <div class="error-message-wrap">
            <label for="editEventTitle" class="required">番組名</label>
            <div id="editEventTitleErrorMessage" class="error-message"></div>
          </div>
          <input type="text" id="editEventTitle" name="eventTitle">
        </div>
        <div class="form-content w-200px">
          <div class="error-message-wrap">
            <label for="editEventManager" class="required">担当者</label>
            <div id="editEventManagerErrorMessage" class="error-message"></div>
          </div>
          <input type="text" id="editEventManager" name="eventManager">
        </div>
      </div>

      <div class="flex">
        <div class="form-content w-200px">
          <div class="error-message-wrap">
            <label for="editEventStart" class="required">開始日</label>
            <div id="editEventStartErrorMessage" class="error-message"></div>
          </div>
          <input type="text" id="editEventStart" name="eventStart">
        </div>
        <div class="form-content w-200px">
          <div class="error-message-wrap">
            <label for="editEventEnd" class="required">終了予定日</label>
            <div id="editEventEndErrorMessage" class="error-message"></div>
          </div>
          <input type="text" id="editEventEnd" name="eventEnd">
        </div>
      </div>

      <div class="flex">
        <div class="form-content w-200px">
          <label for="editRentalHdd" class="required">HDD No.</label>
          <div class="custom-select-wrapper">
            <select id="editRentalHdd" name="rentalHdd"></select>
          </div>
        </div>
        <div class="form-content w-200px">
          <label for="editRentalLocation" class="required">使用場所</label>
          <div class="custom-select-wrapper">
            <select id="editRentalLocation" name="rentalLocation">
              <option value="104" selected>104</option>
              <option value="外部">外部</option>
            </select>
          </div>
        </div>
      </div>

      <div class="form-content w-200px">
        <label for="editRentalCable">ケーブル</label>
        <div class="custom-select-wrapper">
          <select id="editRentalCable" name="rentalCable">
            <option value="USB3.0" selected>USB</option>
            <option value="USB3.0">USB・Thunderbolt</option>
            <option value="Thunderbolt">Thunderbolt</option>
            <option value=""></option>
          </select>
        </div>
      </div>

      <div class="flex">
        <div class="form-content w-200px">
          <div class="error-message-wrap">
            <label for="editReturnDate">返却日</label>
            <div id="editReturnErrorMessage" class="error-message"></div>
          </div>
          <input type="text" id="editReturnDate" class="js-date-field" name="returnDate" required>
        </div>
        <div class="form-content w-150px">
          <label for="editRentalDuration">使用日数</label>
          <input id="editRentalDuration" name="rentalDuration" class="auto-input text-right" readonly>
        </div>
      </div>

      <div class="form-content">
        <label for="editEventNotes">メモ</label>
        <textarea id="editEventNotes" name="eventNotes" rows="2"></textarea>
      </div>
      <div class="flex">
        <button type="submit" class="modal-btn">保存</button>
        <button type="button" class="cancel-btn" id="editEventCancelBtn"
          onclick="document.getElementById('editEventModal').style.display='none';">キャンセル</button>
        <button type="submit" class="delete-btn" name="delete" value="1" onclick="return confirm('本当に削除してよろしいですか？');">
          削除
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  // ---------------------------------------------
  //  カレンダーをflatpickrでカスタマイズ
  // ---------------------------------------------
  function checkAndHide(instance, retries = 10) {
    const dayContainer = instance.calendarContainer.querySelector(".dayContainer");
    const days = dayContainer ? Array.from(dayContainer.querySelectorAll(".flatpickr-day")) : [];
    if (days.length === 42) {
      hideFullNextMonthWeek(instance);
    } else if (retries > 0) {
      requestAnimationFrame(() => checkAndHide(instance, retries - 1));
    }
  }

  // 最終行が全て nextMonthDay なら非表示にする
  function hideFullNextMonthWeek(instance) {
    const dayContainer = instance.calendarContainer.querySelector(".dayContainer");
    const days = Array.from(dayContainer.querySelectorAll(".flatpickr-day"));
    
    if (days.length !== 42) return;

    const lastRowDays = days.slice(35, 42);

    const currentMonth = instance.currentMonth + 1;
    const currentYear = instance.currentYear;

    const isAllNextMonth = lastRowDays.every(day => {
      const ariaLabel = day.getAttribute("aria-label");
      if (!ariaLabel) return false;
      const match = ariaLabel.match(/^(\d+)月\s+(\d+),\s*(\d+)/);
      if (!match) return false;
      const month = parseInt(match[1], 10);
      const year = parseInt(match[3], 10);
      return (month !== currentMonth || year !== currentYear);
    });

    if (isAllNextMonth) {
      lastRowDays.forEach(day => {
        day.style.display = "none";
      });
    }
  }

  function attachFlatpickrWithReset(selector, modalId) {
    flatpickr(selector, {
      locale: "ja",
      dateFormat: "Y-m-d",
      clickOpens: false,
      showDaysInNextMonth: true,
      appendTo: document.getElementById(modalId),
      onReady: function (selectedDates, dateStr, instance) {
        instance.input.addEventListener("click", function () {
          instance.isOpen ? instance.close() : instance.open();
        });

        const resetBtn = document.createElement("button");
        resetBtn.type = "button";
        resetBtn.textContent = "リセット";
        resetBtn.className = "flatpickr-reset-button";
        resetBtn.style.position = "absolute";
        resetBtn.style.right = "10px";
        resetBtn.style.bottom = "15px";

        resetBtn.addEventListener("click", function () {
          instance.clear();
          instance.close();
        });

        instance.calendarContainer.appendChild(resetBtn);
        checkAndHide(instance);
      },
      onMonthChange: function (selectedDates, dateStr, instance) {
        checkAndHide(instance);
      },
      onOpen: function(selectedDates, dateStr, instance) {
        instance.setDate(instance.input.value, false);
        checkAndHide(instance);
      }
    });
  }

  // 開始日
  attachFlatpickrWithReset("#editEventStart", "editEventModal");

  // 終了予定日
  attachFlatpickrWithReset("#editEventEnd", "editEventModal");

  // 返却日
  attachFlatpickrWithReset("#editReturnDate", "editEventModal");
</script>