<div id="addHddModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="document.getElementById('addHddModal').style.display='none'"><i
        class="fa-solid fa-xmark"></i></span>
    <form id="addHddForm" method="post" class="form" action="actions/add_hdd.php">
      <h3>HDD追加</h3>

      <div class="flex">
        <div class="form-content w-300px">
          <div class="error-message-wrap">
            <label for="hddName" class="required">HDD No.</label>
            <div id="addHddNameErrorMessage" class="error-message"></div>
          </div>
          <input type="text" id="hddName" name="hddName" placeholder="10文字以内">
        </div>
      </div>
      <div class="form-content w-200px">
        <label for="addHddCapacity" class="required">容量</label>
        <div class="custom-select-wrapper">
          <select id="addHddCapacity" name="hddCapacity">
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
        <label for="hddNotes">メモ</label>
        <textarea id="hddNotes" name="hddNotes" rows="3"></textarea>
      </div>
      <div class="flex">
        <button type="submit" class="modal-btn">追加</button>
        <button type="button" class="cancel-btn" onclick="document.getElementById('addHddModal').style.display='none';">
          キャンセル
        </button>
      </div>
    </form>
  </div>
</div>