<?php

/* The 3-Clause BSD License
 * 
 * SPDX short identifier: BSD-3-Clause
 *
 * Note: This license has also been called the "New BSD License" or "Modified
 * BSD License". See also the 2-clause BSD License.
 * 
 * Copyright 2015 The Moose Team
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 * 
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Moose\ViewModel;

use Moose\Util\CmnCnst;

/**
 * A button for editing an HTML element with a markdown editor.
 *
 * @author madgaksha
 */
class ButtonFactory extends BaseButton {   
    public static function makeDeleteThread() : ButtonBuilderInterface {
        $id = CmnCnst::BUTTON_DELETE_THREAD;
        return self::createBuilder($id)
                ->setHasCallbackOnClick(true)
                ->setType(self::TYPE_DANGER);
    } 
    
    public static function makeUploadAvatar() : ButtonBuilderInterface {
        return self::createBuilder(CmnCnst::BUTTON_UPLOAD_AVATAR)
                ->setType(BaseButton::TYPE_INFO)
                ->setHasCallbackOnClick(true);
    }
    
    public static function makeCloseDialog() : ButtonBuilderInterface {
        return self::createBuilder(CmnCnst::BUTTON_CLOSE_DIALOG)
                ->addHtmlAttribute('data-dismiss', 'modal');
    }

    public static function makeOpenDialog(string $id, bool $saveData = true) : ButtonBuilderInterface {
        return self::createBuilder(CmnCnst::BUTTON_OPEN_DIALOG)
                ->setHasCallbackOnClick($saveData)
                ->addHtmlAttribute('data-toggle', 'modal')
                ->addHtmlAttribute('data-target', "#$id");
    }  
    
    public static function makeDeletePost() : ButtonBuilderInterface {
        return self::createBuilder('btnDeletePost')
                ->setType(self::TYPE_DANGER)
                ->setHasCallbackOnClick(true);
    }

    public static function makeSubmitButton() : ButtonBuilderInterface {
        return self::createBuilder(CmnCnst::BUTTON_SUBMIT)
                ->setHtmlType('submit');
    }
    
    public static function makeUpdateExam() : ButtonBuilderInterface {
        return self::createBuilder(CmnCnst::BUTTON_UPDATE_EXAM)
                ->setHasCallbackOnClick(true);
    }
    
    public static function makeUpdateSchedule() : ButtonBuilderInterface {
        return self::createBuilder(CmnCnst::BUTTON_UPDATE_SCHEDULE)
                ->setHasCallbackOnClick(true);
    }
    
    public static function makeUpdatePwcd() : ButtonBuilderInterface {
        return self::createBuilder(CmnCnst::BUTTON_UPDATE_PWCD)
                ->setHasCallbackOnClick(true);
    }
    
    public static function makeRemovePwcd() : ButtonBuilderInterface {
        return self::createBuilder(CmnCnst::BUTTON_REMOVE_PWCD)
                ->setType(self::TYPE_DANGER)
                ->setHasCallbackOnClick(true);
    }

    public static function makeDownloadDocumentButton() : ButtonBuilderInterface {
        return self::createBuilder(CmnCnst::BUTTON_DOWNLOAD_DOCUMENT)
                ->setGlyphicon('save')
                ->addHtmlAttribute('target', '_blank')
                ->addHtmlAttribute('download')
                ->setLink('');
    }
    
    public static function makeUpdateDocumentButton() : ButtonBuilderInterface {
        return self::createBuilder(CmnCnst::BUTTON_UPDATE_DOCUMENT)
                ->setHasCallbackOnClick(true)
                ->setGlyphicon('upload');
    }
    
    public static function makeAddDirectoryButton() : ButtonBuilderInterface {
        return self::createBuilder(CmnCnst::BUTTON_ADD_DIRECTORY)
                ->setGlyphicon('plus')
                ->setHasCallbackOnClick(true);
    }
    
    public static function makeDeleteDocumentButton() : ButtonBuilderInterface {
        return self::createBuilder(CmnCnst::BUTTON_DELETE_DOCUMENT)
                ->setHasCallbackOnClick(true)
                ->setType(BaseButton::TYPE_DANGER)
                ->setGlyphicon('remove-sign');
    }
}