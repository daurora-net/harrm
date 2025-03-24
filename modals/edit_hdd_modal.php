<div id="editHddModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="document.getElementById('editHddModal').style.display='none'"><i
        class="fa-solid fa-xmark"></i></span>
    <form id="editHddForm" method="post" class="form" action="actions/edit_hdd.php">
      <h3>HDD編集</h3>
      <input type="hidden" id="editHddId" name="hddId">

      <div class="flex">
        <div class="form-content w-300px">
          <div class="error-message-wrap">
            <label for="editHddName" class="required">HDD No.</label>
            <div id="editHddNameErrorMessage" class="error-message"></div>
          </div>
          <input type="text" id="editHddName" name="hddName" placeholder="10文字以内">
        </div>
      </div>

      <div class="form-content w-100px">
        <label for="editHddCapacity" class="required">容量</label>
        <div class="custom-select-wrapper">
          <select id="editHddCapacity" name="hddCapacity">
            <option value="1TB">1TB</option>
            <option value="2TB">2TB</option>
            <option value="4TB">4TB</option>
            <option value="6TB">6TB</option>
            <option value="8TB">8TB</option>
            <option value="10TB">10TB</option>
            <option value="12TB">12TB</option>
            <option value="14TB">14TB</option>
            <option value="16TB">16TB</option>
            <option value="18TB">18TB</option>
          </select>
        </div>
      </div>

      <div class="form-content">
        <label for="editHddNotes">メモ</label>
        <textarea id="editHddNotes" name="hddNotes" rows="3"></textarea>
      </div>

      <div class="flex">
        <button type="submit" class="modal-btn">保存</button>
        <button type="button" class="cancel-btn"
          onclick="document.getElementById('editHddModal').style.display='none';">
          キャンセル
        </button>
        <button type="submit" class="delete-btn" name="delete" value="1" onclick="return confirm('本当に削除してよろしいですか？');">
          削除
        </button>
      </div>
    </form>
  </div>
</div>