<div id="addRentalModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="document.getElementById('addRentalModal').style.display='none'">
      <i class="fa-solid fa-xmark"></i>
    </span>
    <form id="addRentalForm" method="post" class="form">
      <h3>スケジュール追加</h3>
      <div class="error-message-wrap">
        <div id="addRentalErrorMessage" class="error-message"></div>
      </div>
      <div class="flex">
        <div class="form-content w-300px">
          <div class="error-message-wrap">
            <label for="addRentalTitle" class="required">番組名</label>
            <div id="rentalTitleErrorMessage" class="error-message"></div>
          </div>
          <input type="text" id="addRentalTitle" name="rentalTitle">
        </div>
        <div class="form-content w-200px">
          <div class="error-message-wrap">
            <label for="addRentalManager" class="required">担当者</label>
            <div id="rentalManagerErrorMessage" class="error-message"></div>
          </div>
          <input type="text" id="addRentalManager" name="rentalManager">
        </div>
      </div>

      <div class="flex">
        <div class="form-content w-200px">
          <div class="error-message-wrap">
            <label for="addRentalStart" class="required">開始日</label>
            <div id="rentalStartErrorMessage" class="error-message"></div>
          </div>
          <input type="text" id="addRentalStart" name="rentalStart">
        </div>
        <div class="form-content w-200px">
          <div class="error-message-wrap">
            <label for="addRentalEnd" class="required">終了予定日</label>
            <div id="rentalEndErrorMessage" class="error-message"></div>
          </div>
          <input type="text" id="addRentalEnd" name="rentalEnd">
        </div>
      </div>
      <div class="flex">
        <div class="form-content w-200px">
          <label for="addRentalHdd" class="required">HDD No.</label>
          <div class="custom-select-wrapper">
            <select id="addRentalHdd" name="rentalHdd"></select>
          </div>
        </div>
        <div class="form-content w-200px">
          <label for="addRentalLocation" class="required">使用場所</label>
          <div class="custom-select-wrapper">
            <select id="addRentalLocation" name="rentalLocation">
              <option value="104" selected>104</option>
              <option value="外部">外部</option>
            </select>
          </div>
        </div>
      </div>

      <div class="form-content w-200px">
        <label for="addRentalCable">ケーブル</label>
        <div class="custom-select-wrapper">
          <select id="addRentalCable" name="rentalCable">
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
            <label for="addReturnDate">返却日</label>
            <div id="addReturnErrorMessage" class="error-message"></div>
          </div>
          <input type="text" id="addReturnDate" class="js-date-field" name="returnDate">
        </div>
        <div class="form-content w-150px">
          <label for="addRentalDuration">使用日数</label>
          <input id="addRentalDuration" name="rentalDuration" class="auto-input text-right" readonly>
        </div>
      </div>
      <div class="form-content">
        <label for="addRentalNotes">メモ</label>
        <textarea id="addRentalNotes" name="rentalNotes" rows="2"></textarea>
      </div>
      <div class="flex">
        <button type="submit" class="modal-btn">追加</button>
        <button type="button" class="cancel-btn"
          onclick="document.getElementById('addRentalModal').style.display='none';">
          キャンセル
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  // ---------------------------------------------
  //  カレンダーをflatpickrでカスタマイズ
  // ---------------------------------------------
  // 共通の初期化関数
  function attachFlatpickrWithReset(selector) {
    flatpickr(selector, {
      locale: "ja",
      dateFormat: "Y-m-d",
      defaultDate: document.querySelector(selector).value,
      clickOpens: false,
      appendTo: document.getElementById('addRentalModal'),
      onReady: function (selectedDates, dateStr, instance) {
        instance.input.addEventListener("click", function () {
          if (instance.isOpen) {
            instance.close();
          } else {
            instance.open();
          }
        });

        const resetBtn = document.createElement("button");
        resetBtn.type = "button";
        resetBtn.textContent = "リセット";
        resetBtn.className = "flatpickr-reset-button";
        resetBtn.style.position = "absolute";
        resetBtn.style.right = "10px";
        resetBtn.style.bottom = "10px";

        resetBtn.addEventListener("click", function () {
          instance.clear();
        });

        instance.calendarContainer.appendChild(resetBtn);
      },
      onOpen: function(selectedDates, dateStr, instance) {
        // input にセットされている日付を defaultDate に反映
        instance.setDate(instance.input.value, false);
      }
    });
  }

  // 開始日
  attachFlatpickrWithReset("#addRentalStart");

  // 終了予定日
  attachFlatpickrWithReset("#addRentalEnd");

  // 返却日
  attachFlatpickrWithReset("#addReturnDate");
</script>