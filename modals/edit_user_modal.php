<div id="editUserModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="document.getElementById('editUserModal').style.display='none'">
      <i class="fa-solid fa-xmark"></i>
    </span>
    <form id="editUserForm" method="post" class="form" action="actions/edit_user.php">
      <h3>ユーザー編集</h3>
      <input type="hidden" id="editUserId" name="userId">
      <div class="form-content">
        <div class="error-message-wrap">
          <label for="editUsername" class="required">ユーザー名</label>
          <div id="editUsernameErrorMessage" class="error-message"></div>
        </div>
        <input type="text" id="editUsername" name="username">
      </div>
      <div class="flex">
        <button type="submit" class="modal-btn">保存</button>
        <button type="button" class="cancel-btn"
          onclick="document.getElementById('editUserModal').style.display='none';">
          キャンセル
        </button>
        <button type="submit" class="delete-btn" name="delete" value="1"
          onclick="return confirm('本当に削除してよろしいですか？');">削除</button>
      </div>
    </form>
  </div>
</div>