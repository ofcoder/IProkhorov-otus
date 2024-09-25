<?php

use Bitrix\Main\Context;

$request = Context::getCurrent()->getRequest();

if ($request->getRequestedPage() == '/bitrix/admin/learn_test_admin.php'):
    $courseId  = $request['COURSE_ID'];
    $lessonId  = $request['PARENT_LESSON_ID'];
    $lessonPath  = $request['LESSON_PATH'];
    $copyTestLink = "/bitrix/admin/kb_learn_copy_test.php?COURSE_ID={$courseId}&PARENT_LESSON_ID={$lessonId}&LESSON_PATH={$lessonPath}";
    ?>
<script>
    document.addEventListener("DOMContentLoaded", (event) => {
        const contextMenu = document.querySelector(".adm-list-table-top");
        const addBtn = document.getElementById("btn_new");
        const settingsBtn = contextMenu.querySelector(".adm-table-setting");
        const copyBtn = addBtn.cloneNode(true);
        copyBtn.setAttribute("id", "btn_copy");
        copyBtn.setAttribute("title", "Копировать");
        copyBtn.setAttribute("href", "<?= $copyTestLink;?>");
        copyBtn.innerText = "Копировать";
        const insertedElement = contextMenu.insertBefore(copyBtn, settingsBtn);
    });
</script>
<?php endif;
