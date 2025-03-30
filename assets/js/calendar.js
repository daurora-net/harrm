document.addEventListener('DOMContentLoaded', function () {
  var calendarEl = document.getElementById('calendar');
  var resourcesData = {};

  // +++++++++++++++++++++++++++++++++++++++++++++++++++++
  // 「編集モーダルのキャンセル」が発火したらイベントをrevert & refetch
  // +++++++++++++++++++++++++++++++++++++++++++++++++++++
  window.addEventListener('cancelEditEventModal', function () {
    // currentCalendarActionがドラッグorリサイズ由来なら revert() と refetchEvents() する
    if (window.currentCalendarAction
      && (window.currentCalendarAction.type === 'drop'
        || window.currentCalendarAction.type === 'resize')) {

      window.currentCalendarAction.info.revert();

      if (window.calendar) {
        window.calendar.refetchEvents();
      }
    }

    window.currentCalendarAction = null;
  });
  // +++++++++++++++++++++++++++++++++++++++++++++++++++++

  window.currentCalendarAction = null;

  var calendar = new FullCalendar.Calendar(calendarEl, {
    timeZone: 'UTC',
    initialView: 'resourceTimelineMonth',
    aspectRatio: 1.5,
    height: 'auto',
    headerToolbar: {
      left: '',
      right: 'today,prev,next'
    },
    editable: true, // イベントをドラッグ移動、リサイズできる
    resourceAreaHeaderContent: 'HDD No.',
    resourceOrder: 'name',
    resourceAreaWidth: "200px",

    selectable: true,
    // ---------------------------------------------
    // ドラッグで日付範囲を選択したときのコールバック
    // ---------------------------------------------
    select: function (info) {
      calendar.unselect();

      var startDateStr = info.startStr.slice(0, 10);
      var endDateObj = new Date(info.endStr);
      endDateObj.setDate(endDateObj.getDate() - 1); // FullCalendarの都合で+1日されている
      var endDateStr = endDateObj.toISOString().slice(0, 10);

      var addRentalModal = document.getElementById("addRentalModal");
      if (addRentalModal) {
        document.getElementById("addRentalStart").value = startDateStr;
        document.getElementById("addRentalEnd").value = endDateStr;
        addRentalModal.style.display = "block";
      }
    },

    dateClick: function (info) {
      // 新規追加モーダルを開く。開始日にクリックした日付を設定
      var addRentalModal = document.getElementById("addRentalModal");
      if (addRentalModal) {
        document.getElementById("addRentalStart").value = info.dateStr.slice(0, 10);
        // クリックされた箇所のリソース（HDD）IDを取得（存在する場合）
        var clickedResourceId = info.resource ? info.resource.id : null;
        fetch('actions/fetch_available_resources.php?current_rental_id=0')
          .then(response => response.json())
          .then(data => {
            var hddSelect = document.getElementById("addRentalHdd");
            if (hddSelect) {
              hddSelect.innerHTML = '';
              data.forEach(function (resource) {
                var option = document.createElement("option");
                option.value = resource.id;
                option.textContent = resource.name;
                hddSelect.appendChild(option);
              });
              // クリックされたリソースIDが取得できていれば選択状態にする
              if (clickedResourceId) {
                hddSelect.value = clickedResourceId;
              }
            }
            addRentalModal.style.display = "block";
          })
          .catch(error => {
            console.error("利用可能なHDDリソース取得エラー (dateClick):", error);
            addRentalModal.style.display = "block";
          });
      }
    },

    // ---------------------------------------------
    // イベントをドラッグ移動した直後のコールバック
    // ---------------------------------------------
    eventDrop: function (info) {
      // まず revert() 用に info を保管
      window.currentCalendarAction = { type: 'drop', info: info };

      var movedEvent = info.event;
      var newResource = info.newResource;
      var newResourceId = (newResource && newResource.id)
        ? newResource.id
        : (movedEvent.getResources()[0] ? movedEvent.getResources()[0].id : null);

      var newStart = movedEvent.start;
      var newEnd = movedEvent.end;
      var startStr = newStart ? newStart.toISOString().slice(0, 10) : '';

      var adjustedEnd = newEnd ? new Date(newEnd.getTime()) : null;
      if (adjustedEnd) {
        adjustedEnd.setDate(adjustedEnd.getDate() - 1);
      }
      var adjustedEnd = newEnd ? new Date(newEnd.getTime()) : null;
      if (adjustedEnd) {
        adjustedEnd.setDate(adjustedEnd.getDate() - 1);
      }
      var endStr = adjustedEnd
        ? adjustedEnd.toISOString().slice(0, 10)
        : startStr;

      var editModal = document.getElementById("editEventModal");
      if (!editModal) {
        console.error("editEventModal が見つかりません。");
        return;
      }
      editModal.style.display = "block";

      // フォームへセット
      document.getElementById("editEventId").value = movedEvent.id;
      document.getElementById("editEventTitle").value = movedEvent.title;
      document.getElementById("editEventManager").value = movedEvent.extendedProps.manager || "";
      // 返却日があるかで処理を分ける
      if (movedEvent.extendedProps.return_date) {
        // 既に返却日があれば「開始日と返却日だけ」を変更し、終了予定日は変更しない
        document.getElementById("editEventStart").value = startStr;
        document.getElementById("editReturnDate").value = endStr;
        document.getElementById("editReturnDate").dispatchEvent(new Event("input"));   
        // 終了予定日は元の値を表示させたいので、real_end から再セット
        if (movedEvent.extendedProps.real_end) {
          document.getElementById("editEventEnd").value = movedEvent.extendedProps.real_end;
        }
      } else {
        // 返却日がなければ「開始日と終了予定日」を変更し、返却日は空のまま
        document.getElementById("editEventStart").value = startStr;
        document.getElementById("editEventEnd").value = endStr;
      }
      // 関係ない項目はイベントオブジェクトの既存値をそのままセット
      document.getElementById("editRentalLocation").value = movedEvent.extendedProps.location || "";
      document.getElementById("editRentalCable").value = movedEvent.extendedProps.cable || "";
      document.getElementById("editEventNotes").value = movedEvent.extendedProps.notes || "";
      
      // HDDリストの再取得→新HDDを選択
      fetch(`actions/fetch_available_resources.php?current_rental_id=${movedEvent.id}`)
        .then(response => response.json())
        .then(data => {
          fetch(`actions/fetch_available_resources.php?current_rental_id=${movedEvent.id}`)
          var hddSelect = document.getElementById("editRentalHdd");
          if (!hddSelect) return;
          hddSelect.innerHTML = '';
          data.forEach(function (res) {
            var option = document.createElement("option");
            option.value = res.id;
            option.textContent = res.name;
            hddSelect.appendChild(option);
          });
          hddSelect.value = newResourceId;
        })
        .catch(err => {
          console.error("fetch_available_resourcesエラー (eventDrop):", err);
        });
    },

    // ---------------------------------------------
    // イベントのリサイズ後（両端をドラッグで日付拡縮）
    // ---------------------------------------------
    eventResize: function (info) {
      window.currentCalendarAction = { type: 'resize', info: info };

      var resizedEvent = info.event;
      var newStart = resizedEvent.start;
      var newEnd = resizedEvent.end;

      var resources = resizedEvent.getResources();
      var resourceId = resources.length > 0 ? resources[0].id : null;

      var startStr = newStart ? newStart.toISOString().slice(0, 10) : '';

      // 1日多い問題を修正：end から1日引く
      var adjustedEnd = newEnd ? new Date(newEnd.getTime()) : null;
      if (adjustedEnd) {
        adjustedEnd.setDate(adjustedEnd.getDate() - 1);
      }
      var endStr = adjustedEnd
        ? adjustedEnd.toISOString().slice(0, 10)
        : startStr;

      var editModal = document.getElementById("editEventModal");
      if (!editModal) {
        console.error("editEventModal が見つかりません。");
        return;
      }
      editModal.style.display = "block";

      // フォームへセット
      document.getElementById("editEventId").value = resizedEvent.id;
      document.getElementById("editEventTitle").value = resizedEvent.title;
      document.getElementById("editEventManager").value = resizedEvent.extendedProps.manager || "";
      // 返却日があるかで処理を分ける
      if (resizedEvent.extendedProps.return_date) {
        // 既に返却日がある場合は、開始日と返却日のみ変更
        document.getElementById("editEventStart").value = startStr;
        document.getElementById("editReturnDate").value = endStr;
        document.getElementById("editReturnDate").dispatchEvent(new Event("input"));
        // 終了予定日は元のままにする（real_end があれば戻す）
        if (resizedEvent.extendedProps.real_end) {
          document.getElementById("editEventEnd").value = resizedEvent.extendedProps.real_end;
        }
      } else {
        // 返却日がなければ終了予定日を更新
        document.getElementById("editEventStart").value = startStr;
        document.getElementById("editEventEnd").value = endStr;
      }
      // 変更対象外の項目はそのままの値を維持
      document.getElementById("editRentalLocation").value = resizedEvent.extendedProps.location || "";
      document.getElementById("editRentalCable").value = resizedEvent.extendedProps.cable || "";
      document.getElementById("editEventNotes").value = resizedEvent.extendedProps.notes || "";

      fetch(`actions/fetch_available_resources.php?current_rental_id=${resizedEvent.id}`)
        .then(response => response.json())
        .then(data => {
          data.sort((a, b) => a.name.localeCompare(b.name, 'en', { numeric: true }));
          var hddSelect = document.getElementById("editRentalHdd");
          if (!hddSelect) return;
          hddSelect.innerHTML = '';
          data.forEach(function (res) {
            var option = document.createElement("option");
            option.value = res.id;
            option.textContent = res.name;
            hddSelect.appendChild(option);
          });
          hddSelect.value = resourceId;
        })
        .catch(err => {
          console.error("fetch_available_resourcesエラー (eventResize):", err);
        });
    },

    resourceLabelContent: function (arg) {
      return resourcesData[arg.resource.id]
        ? resourcesData[arg.resource.id]
        : arg.resource.id;
    },

    resources: function (fetchInfo, successCallback, failureCallback) {
      fetch('actions/fetch_resources.php')
        .then(response => response.json())
        .then(data => {
          data.sort((a, b) => {
            const aNameMatch = a.name.match(/^([A-Za-z]+)(\d+)$/);
            const bNameMatch = b.name.match(/^([A-Za-z]+)(\d+)$/);

            const aPrefix = aNameMatch ? aNameMatch[1] : '';
            const bPrefix = bNameMatch ? bNameMatch[1] : '';
            const prefixCompare = aPrefix.localeCompare(bPrefix);
            if (prefixCompare !== 0) {
              return prefixCompare;
            }

            const aNum = aNameMatch ? parseInt(aNameMatch[2], 10) : 0;
            const bNum = bNameMatch ? parseInt(bNameMatch[2], 10) : 0;
            return aNum - bNum;
          });

          resourcesData = data.reduce((acc, resource) => {
            acc[resource.id] = resource.name + (resource.capacity ? '＿' + resource.capacity : '');
            return acc;
          }, {});
          successCallback(data);
        })
        .catch(error => {
          failureCallback(error);
        });
    },

    events: 'actions/fetch_events.php',
    slotLabelFormat: [
      { year: 'numeric', month: 'numeric' },
      { day: 'numeric' }
    ],
    slotLabelContent: ({ date, level }) => {
      const year = date.getUTCFullYear();
      const month = date.getUTCMonth() + 1;
      const day = date.getUTCDate();
      return { html: level === 0 ? `${year}-${month}` : `${day}` };
    },
    eventTimeFormat: {
      hour: false,
      minute: false,
      meridiem: false
    },
    eventContent: function (arg) {
      var resources = arg.event.getResources();
      var resourceName = '';
      if (resources.length > 0) {
        resourceName = resources[0].title || resources[0].name || resourcesData[resources[0].id];
      }

      var eventTitle = document.createElement('div');
      eventTitle.innerHTML = arg.event.title;

      var eventManager = document.createElement('div');
      eventManager.innerHTML = arg.event.extendedProps.manager;

      var eventResource = document.createElement('div');
      eventResource.innerHTML = resourceName;

      return { domNodes: [eventTitle, eventManager, eventResource] };
    },

    eventClick: function (info) {
      var eventObj = info.event;
      var resources = eventObj.getResources();
      var resourceId = resources.length > 0 ? resources[0].id : undefined;

      var modal = document.getElementById("editEventModal");
      if (!modal) {
        console.log("editEventModal がページに存在しません。");
        return;
      }
      modal.style.display = "block";

      // 利用可能なHDDリソースを取得してセレクトボックスを更新
      if (resourceId) {
        fetch(`actions/fetch_available_resources.php?current_rental_id=${eventObj.id}`)
          .then(response => response.json())
          .then(data => {
            data.sort((a, b) => a.name.localeCompare(b.name, 'en', { numeric: true }));
            var hddSelect = document.getElementById("editRentalHdd");
            hddSelect.innerHTML = '';

            data.forEach(function (resource) {
              var option = document.createElement("option");
              option.value = resource.id;
              option.textContent = resource.name;
              hddSelect.appendChild(option);
            });

            hddSelect.value = resourceId;
          })
          .catch(error => {
            console.error("利用可能なHDDリソースの取得エラー:", error);
          });
      }

      document.getElementById("editEventTitle").value = eventObj.title;
      document.getElementById("editEventManager").value = eventObj.extendedProps.manager;
      document.getElementById("editEventStart").value = eventObj.start
        ? eventObj.start.toISOString().slice(0, 10)
        : "";

      // realEnd
      var realEnd = eventObj.extendedProps.real_end;
      if (!realEnd) {
        realEnd = eventObj.start
          ? eventObj.start.toISOString().slice(0, 10)
          : "";
      }
      document.getElementById("editEventEnd").value = realEnd;
      document.getElementById("editEventId").value = eventObj.id;

      var retField = document.getElementById("editReturnDate");
      if (retField) {
        retField.value = eventObj.extendedProps.return_date
          ? eventObj.extendedProps.return_date.slice(0, 10)
          : "";
        retField.dispatchEvent(new Event("input"));
      }

      document.getElementById("editRentalLocation").value =
        eventObj.extendedProps.location || "";
      document.getElementById("editRentalCable").value =
        eventObj.extendedProps.cable || "";

      var durationField = document.getElementById("editRentalDuration");
      if (durationField) {
        if (eventObj.extendedProps.is_returned == 0) {
          durationField.value = "";
        } else {
          durationField.value = eventObj.extendedProps.duration || "";
        }
      }

      document.getElementById("editEventNotes").value =
        eventObj.extendedProps.notes || "";
    },

    // ---------------------------------------------
    // 「使用場所」項目に応じたクラス名を付与
    // ---------------------------------------------
    eventClassNames: function (arg) {
      var classes = [];
      if (arg.event.extendedProps.is_returned == 1) {
        classes.push('returned-event');
      }
      var loc = arg.event.extendedProps.location;
      if (loc === '104') {
        classes.push('location-104');
      } else if (loc === '外部') {
        classes.push('location-gaibu');
      }
      return classes;
    }
  });

  window.calendar = calendar;
  calendar.render();

  // ---------------------------------------------
  // 月セレクトボタン
  // ---------------------------------------------
  (function () {
    var todayButton = document.querySelector('.fc-today-button');
    if (!todayButton) return;

    var monthSelectInput = document.createElement('input');
    monthSelectInput.type = 'text';
    monthSelectInput.id = 'monthSelect';
    monthSelectInput.className = "flatpickr-month-select";
    monthSelectInput.name = 'ym';
    monthSelectInput.placeholder = "\uf274";

    flatpickr(monthSelectInput, {
      locale: "ja",
      clickOpens: false,
      plugins: [
        new monthSelectPlugin({
          shorthand: false,
          dateFormat: "Y-m",
          altFormat: "Y年n月"
        })
      ],
      defaultDate: "",
      onReady: function (selectedDates, dateStr, instance) {
        instance.input.addEventListener("click", function () {
          if (instance.isOpen) {
            instance.close();
          } else {
            instance.open();
          }
        });
        // リセットで選択をクリアし、今月表示に戻す
        var resetBtn = document.createElement("button");
        resetBtn.className = "flatpickr-reset-button";
        resetBtn.textContent = "リセット";
        resetBtn.type = "button";
        resetBtn.addEventListener("click", function () {
          instance.clear();
          instance.input.value = "\uf274";
          var now = new Date();
          var currentYear = now.getFullYear();
          calendar.gotoDate(new Date(currentYear, now.getMonth() + 1, 1));
          instance.close();
        });
        instance.calendarContainer.appendChild(resetBtn);
      },
      onChange: function (selectedDates, dateStr, instance) {
        if (selectedDates.length > 0) {
          var selectedDate = selectedDates[0];
          // 選択された月が1ヶ月前になる問題を回避
          var adjustedDate = new Date(selectedDate.getFullYear(), selectedDate.getMonth() + 1, 1);
          calendar.gotoDate(adjustedDate);
          instance.input.value = "\uf274";
        }
      }
    });

    todayButton.parentNode.insertBefore(monthSelectInput, todayButton.nextSibling);
  })();
});