<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/settings/">Настройки</a></li>
            <li class="breadcrumb-item active" aria-current="page">Общее</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-body">
            <div class="search_form_tab d-none" style="margin-top:-9px">
             <div class="buttonsprofile albumsbuttonsprofile buttonsprofileSecond" style="height:22px">
              <div class="buttonsprofileSec"><a href="/settings/" onClick="Page.Go(this.href); return false;"><div><b>Общее</b></div></a></div>
              <a href="/settings/privacy/" onClick="Page.Go(this.href); return false;"><div><b>Приватность</b></div></a>
              <a href="/settings/blacklist/" onClick="Page.Go(this.href); return false;"><div><b>Черный список</b></div></a>
             </div>
            </div>
            <div class="err_yellow name_errors {code-1}" style="font-weight:normal;margin-top:25px">Код активации из письма с текущего почтового ящика принят. Осталось подтвердить код активации в письме, отправленном на новый почтовый ящик.</div>
            <div class="err_yellow name_errors {code-2}" style="font-weight:normal;margin-top:25px">Код активации из письма с нового почтового ящика принят. Осталось подтвердить код активации в письме, отправленном на текущий почтовый ящик.</div>
            <div class="err_yellow name_errors {code-3}" style="font-weight:normal;margin-top:25px">Адрес Вашей электронной почты был успешно изменен на новый.</div>
            <div class="margin_top_10"></div><div class="allbar_title">Изменить пароль</div>
            <div class="err_red no_display pass_errors" id="err_pass_1" style="font-weight:normal;">Пароль не изменён, так как прежний пароль введён неправильно.</div>
            <div class="err_red no_display pass_errors" id="err_pass_2" style="font-weight:normal;">Пароль не изменён, так как новый пароль повторен неправильно.</div>
            <div class="err_yellow no_display pass_errors" id="ok_pass" style="font-weight:normal;">Пароль успешно изменён.</div>
            <div class="texta">Старый пароль:</div><input type="password" id="old_pass" class="inpst" maxlength="100" style="width:150px;" /><span id="validOldpass"></span><div class="mgclr"></div>
            <div class="texta">Новый пароль:</div><input type="password" id="new_pass" class="inpst" maxlength="100" style="width:150px;" onMouseOver="myhtml.title('', 'Пароль должен быть не менее 6 символов в длину', 'new_pass')" /><span id="validNewpass"></span><div class="mgclr"></div>
            <div class="texta">Повторите пароль:</div><input type="password" id="new_pass2" class="inpst" maxlength="100" style="width:150px;" onMouseOver="myhtml.title('', 'Введите еще раз новый пароль', 'new_pass2')" /><span id="validNewpass2"></span><div class="mgclr"></div>
            <div class="texta">&nbsp;</div>
                <button onClick="settings.saveNewPwd(); return false" id="saveNewPwd" class="btn btn-success">Изменить пароль</button>
            <div class="mgclr"></div>
            <div class="margin_top_10"></div><div class="allbar_title">Изменить имя</div>
            <div class="err_red no_display name_errors" id="err_name_1" style="font-weight:normal;">Специальные символы и пробелы запрещены.</div>
            <div class="err_yellow no_display name_errors" id="ok_name" style="font-weight:normal;">Изменения успешно сохранены.</div>
            <div class="texta">Ваше имя:</div><input type="text" id="name" class="inpst" maxlength="100"  style="width:150px;" value="{name}" /><span id="validName"></span><div class="mgclr"></div>
            <div class="texta">Ваша фамилия:</div><input type="text" id="lastname" class="inpst" maxlength="100"  style="width:150px;" value="{lastname}" /><span id="validLastname"></span><div class="mgclr"></div>
            <div class="texta">&nbsp;</div>
                <button onClick="settings.saveNewName(); return false" id="saveNewName" class="btn btn-success">Изменить имя</button>
            <div class="mgclr"></div>
            <div class="margin_top_10"></div><div class="allbar_title">Адрес Вашей электронной почты</div>
            <div class="err_yellow name_errors no_display" id="ok_email" style="font-weight:normal;">На <b>оба</b> почтовых ящика придут письма с подтверждением.</div>
            <div class="err_red no_display name_errors" id="err_email" style="font-weight:normal;">Неправильный email адрес</div>
            <div class="texta">Текущий адрес:</div><div style="color:#555;margin-top:13px;margin-bottom:10px">{email}</div><div class="mgclr"></div>
            <div class="texta">Новый адрес:</div><input type="text" id="email" class="inpst" maxlength="100" style="width:150px;" /><span id="validName"></span><div class="mgclr"></div>
            <div class="texta">&nbsp;</div>
                <button onClick="settings.savenewmail(); return false" id="saveNewEmail" class="btn btn-success">Сохранить адрес</button>
            <div class="mgclr"></div>

            <div class="allbar_title">Региональные настройки</div>
            <div class="err_yellow no_display" id="ok_timez" style="font-weight:normal;">Часовой пояс успешно сохранён.</div>
            <div class="texta">Часовой пояс:</div>
            <select id="timezona" class="inpst" style="width:200px">
                <option value="0">- Не выбрано -</option>
                {timezs}
            </select>
            <div class="mgclr"></div>
            <div class="texta">&nbsp;</div><div class="button_div fl_l"><button onClick="settings.savetimezona(); return false" id="saveTimezona">Сохранить</button></div>
            <div class="mgclr"></div>
                        
            <div class="nSDelPg">Вы можете <a class="cursor_pointer" onClick="delMyPage()">удалить свою страницу</a>.</div>
        </div>
    </div>
</div>
