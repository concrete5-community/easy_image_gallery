<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Block\View\BlockView $view
 * @var Concrete\Package\EasyImageGallery\Block\EasyImageGallery\Controller $controller
 * @var Concrete\Core\Validation\CSRF\Token $token
 * @var Concrete\Core\Form\Service\Form $form
 * @var Concrete\Core\Form\Service\Widget\Color $colorWidget
 *
 * @var Concrete\Package\EasyImageGallery\Options $options
 * @var Concrete\Core\File\Set\Set[] $fileSets
 * @var array $fDetails
 * @var bool $isComposer
 */

?>
<ul class="ccm-inline-toolbar ccm-ui easy-image-toolbar">
    <li class="ccm-sub-toolbar-text-cell">
    <?php
        if ($fileSets !== []) {
            ?>
            <label for="fsID"><?= t('Add a File Set') ?>:</label>
            <select name="fsID" multiple id="fsID" style="width:300px" data-placeholder="<?= t('Choose') ?>">
                <?php
                foreach ($fileSets as $fs) {
                    $fsID = (int) $fs->getFileSetID();
                    ?>
                    <option value="<?= $fsID ?>" <?= in_array($fsID, $options->fsIDs, true) ? 'selected' : '' ?>><?= h($fs->getFileSetName()) ?></option>
                    <?php
                }
                ?>
            </select>
            <?php
        } else {
            ?>
            <label for="fsID"><?= t('No File Set') ?></label>
            <?php
        }
        ?>
    </li>
    <li class="ccm-inline-toolbar-button ccm-inline-toolbar-button-options">
        <button id="options-button" type="button" class="btn btn-mini"><?= t('Options') ?></button>
    </li>
    <li class="ccm-inline-toolbar-button ccm-inline-toolbar-button-options">
        <button id="advanced-options-button" type="button" class="btn btn-mini"><?= t('Adv Opt') ?></button>
    </li>
    <li class="ccm-inline-toolbar-button ccm-inline-toolbar-button-sort">
        <button id="sort-button" type="button" class="btn btn-mini"><?= t('Sort') ?></button>
    </li>
    <li class="ccm-inline-toolbar-button ccm-inline-toolbar-button-cancel">
        <button id="cancel-button" type="button" class="btn btn-mini"><?= t('Cancel') ?></button>
    </li>
    <?php
    if (!$isComposer) {
        ?>
        <li class="ccm-inline-toolbar-button ccm-inline-toolbar-button-save">
            <button id="easy_image_save" class="btn btn-primary" type="button">
                <?php
                if ($controller->getAction() === 'add') {
                    echo t('Add Gallery');
                } else {
                    echo t('Update Gallery');
                }
                ?>
            </button>
        </li>
        <?php
    }
    ?>
</ul>

<?php $view->inc('advanced_options.php') ?>

<div class="basic-image-form-wrapper ccm-ui">
    <div class="easy_image-items"></div>
</div>

<script type="text/template" id="imageTemplate">
    <div class="image-item block-to-sort <% if (image_url.length > 0) { %>filled fid-<%= fID %> <% } %> ccm-ui <%= classes %>" <% if (originType == 'fileset') { %>rel="<%=filesetName%>" <% } %>>
        <div id="manage-file" class="manage-file">
            <% if (image_url.length > 0) { %>
                <div class="img" style="background-image:url(<%= image_url %>)"></div>
                <div class="item-toolbar">
                    <h4 data-type="textarea" data-name="fvTitle" class="editable editable-click" title="<?= t('Title') ?>"><%= title %></h4>
                    <p><strong><?= t('Description') ?>:</strong><span class="description editable editable-click" data-placeholder="<?= t('Write your description') ?>" data-name="fvDescription" data-type="textarea" <% if (!description) { %> editable-empty <% } %>><%= description %></span></p>
                    <hr class="separator">
                    <p><strong><?= t('Link type') ?>:</strong><span class="link_type editable editable-click" data-placeholder="<?= t('Link type') ?>" data-value="<%= link_type %>" data-name="link_type" data-type="select" data-source='{"None": "None", "URL":"External URL", "Page": "Link to page"}' <% if (!link_type) { %> editable-empty <% } %>><%= link_type %></span></p>
                    <p class="entry-link-url" style="<% if (link_type != 'URL') { %> display: none;<% } %>"><strong><?= t('External URL') ?> : </strong><span data-field="entry-link-url" data-type="textarea" data-name="external_link_url" class="editable editable-click" data-placeholder="<?= t('http://') ?>" title="<?= t('External URL') ?>"><%=external_link_url%></span></p>
                    <div style="<% if (link_type != 'Page') { %> display: none;<% } %>" data-field="entry-link-page-selector" class="form-group">
                        <label><?= t('Choose Page') ?>:</label>
                        <div data-field="entry-link-page-selector-select"></div>
                    </div>
                    <a href="javascript:void(0)" class="remove-item"><i class="fa fa-remove"></i></a>
                    <div class="item-controls">
                        <a class="dialog-launch item-properties" dialog-modal="true" dialog-width="600" dialog-height="400" dialog-title="Properties" href="<?= URL::to('/ccm/system/dialogs/file/properties') ?>?fID=<%= fID %>"><i class="fa fa-gear"></i></a>
                        <a class="handle"><i class="fa fa-arrows"></i></a>
                    </div>
                </div>
                <input type="hidden" name="<?= $view->field('fID') ?>[]" class="image-fID" value="<%=inputValue%>" />
                <input type="hidden" name="<?= $view->field('uniqueFID') ?>[]" class="unique-image-fID" value="<%=fID%>" />
            <% } else { %>
                <div class="add-file-control block-to-sort">
                    <a href="#" class="upload-file"><i class="fa fa-upload"></i></a><a href="#" class="add-file"><i class="fa fa-th-list"></i></a>
                    <h4 style="display:none">zzzz</h4>
                </div>
                <span class="process"><?= t('Processing') ?> <i class="fa fa-cog fa-spin"></i></span>
                <input type="text" class="knob" value="0" data-width="150" data-height="150" data-fgColor="#555" data-readOnly="1" data-bgColor="#e1e1e1" data-thickness=".1" />
                <input type="file" name="files[]" class="browse-file" multiple />
            <% } %>
        </div>
    </div>
</script>

<script>
$(document).ready(function() {
'use strict';

window.CCM_EDITOR_SECURITY_TOKEN = <?= json_encode($token->generate('editor')) ?>;

$.fn.replaceWithPush = function(a) {
    const $a = $(a);
    this.replaceWith($a);
    return $a;
};

const _templateSlide = _.template($('#imageTemplate').html());

const sliderEntriesContainer = $('.easy_image-items');

let selectedFilesets = <?= json_encode($options->fsIDs) ?>;

let is_first_file = true;

function sortUsingNestedText(parent, childSelector, keySelector) {
    const items = parent.children(childSelector).detach().sort(function(a, b) {
        const vA = $(keySelector, a).text();
        const vB = $(keySelector, b).text();
        return (vA < vB) ? -1 : (vA > vB) ? 1 : 0;
    });
    parent.append(items);
}

function attachUploadEvent($obj) {
    $obj.fileupload({
        url: CCM_DISPATCHER_FILENAME + '/ccm/system/file/upload',
        dataType: 'json',
        formData: {ccm_token: CCM_SECURITY_TOKEN},
        add(e, data) {
            const uploadFile = data.files[0];
            if (!(/\.(gif|jpg|jpeg|tiff|png)$/i).test(uploadFile.name)) {
                window.alert(<?= json_encode(t('You must select an image file only')) ?>);
                return;
            }
            if (uploadFile.size > 6000000) {
                window.alert(<?= json_encode(t('Please upload a smaller image, max size is 6 MB')) ?>);
                return;
            }
            data.submit();
        },
        send(e, data) {
            if (is_first_file) {
                initUploadActionOnItem($(e.target));
                is_first_file = false;
            } else {
                data.newItem = fillTemplate();
                initUploadActionOnItem(data.newItem);
            }
        },
        progress(e, data) {
            const progress = parseInt(data.loaded / data.total * 100, 10);
            const target = data.newItem || $(e.target);
            if (progress < 95) {
                target.find('.knob').val(progress).change();
            } else {
                target.find('.knob').val(100).change();
                if(!target.find('canvas').is('.out')) {
                    target.find('canvas').addClass('out');
                    target.find('.process').addClass('in');
                }
            }
        },
        done(e, data) {
            const target = data.newItem || $(e.target);
            $.post(
                <?= json_encode((string) $controller->getActionURL('getFileDetails')) ?>,
                {
                    <?= json_encode($token::DEFAULT_TOKEN_NAME) ?>: <?= json_encode($token->generate('eig_getFileDetails')) ?>,
                    fID: data.result[0].fID,
                },
                function(file) {
                    fillTemplate(file,target);
                },
                'json'
            );
        },
        fail(r, data) {
            let message;
            try {
                message = JSON.parse(r.responseText).errors.join('<br/>');
            } catch (e) {
                message = r.responseText;
            }
            window.ConcreteAlert.dialog('Error', message);
        },
        stop(e) {
            is_first_file = true;
            fillTemplate();
        }
    })
    const $inputfile = $obj.find('input.browse-file');
    $obj.find('.upload-file').on('click', function(e) {
        e.preventDefault();
        $inputfile.click();
    });
}

function initUploadActionOnItem($obj) {
    $obj.find('.knob').knob();
    $obj.find('.add-file-control').hide();
}

function attachDelete($obj) {
    $obj.find('.remove-item').click(function() {
        if (window.confirm(<?= json_encode(t('Are you sure to delete this image?')) ?>)) {
            $(this).closest('.image-item').remove();
            refreshManager();
        }
    });
}

function attachFileManagerLaunch($obj) {
    $obj.find('.add-file').click(function(event) {
        event.preventDefault();
        ConcreteFileManager.launchDialog(function (data) {
            $.post(
                <?= json_encode((string) $controller->getActionURL('getFileDetails')) ?>,
                {
                    <?= json_encode($token::DEFAULT_TOKEN_NAME) ?>: <?= json_encode($token->generate('eig_getFileDetails')) ?>,
                    fID: data.fID,
                },
                function (file) {
                    if (file.generic_type == '1') {
                        $.fn.dialog.hideLoader();
                        fillTemplate(file, $obj);
                        fillTemplate();
                        return;
                    }
                    $.fn.dialog.hideLoader();
                    window.alert(<?= json_encode(t('You must select an image file only')) ?>);
                },
                'json'
            );
        });
    });
}

function initImageEdit($obj, file) {
    $obj.find('.dialog-launch').dialog();
    $obj.find('.editable-click').editable({
        ajaxOptions: {dataType: 'json'},
        emptytext: <?= json_encode(t('None')) ?>,
        showbuttons: true,
        url: <?= json_encode((string) $controller->getActionURL('saveField')) ?>,
        params: {
            <?= json_encode($token::DEFAULT_TOKEN_NAME) ?>: <?= json_encode($token->generate('eig_saveField')) ?>,
            fID: file.fID,
        },
        pk: '_x',
        success(data) {
            const container = $(this).closest('.image-item');
            if(data.name == 'link_type') {
                displayLinkChooser(container,data.value);
            };
        }
    });
    $('#sort-button').click(function() {
        sortUsingNestedText($('.easy_image-items'), 'div.block-to-sort', 'h4');
    });
    if (file.link_type) {
        displayLinkChooser($obj, file.link_type);
    }
    $obj.find('.editable-click').on('shown', function (data) {
        $(data.target).closest('.item-toolbar').addClass('active');
    });
    $obj.find('.editable-click').on('hidden', function (data) {
        $(data.target).closest('.item-toolbar').removeClass('active');
    });
};

function fillTemplate(file, $element) {
    const defaults = {
        fID: '',
        fsID:'',
        title: '',
        link_url: '',
        internal_link_cid: undefined,
        link_type: '',
        cID: '',
        description: '',
        sort_order: '',
        image_url: '',
        originType: 'file',
    };
    if (file) {
        $.extend(
            defaults,
            {
                fID: file.fID,
                fsID: file.fsID,
                title: file.title,
                description: file.description,
                sort_order: '',
                image_url: file.urlInline,
                internal_link_cid: file.internal_link_cid,
                external_link_url: file.external_link_url,
                link_type: file.link_type,
                originType: file.originType,
                filesetName: file.filesetName,
            }
        );
    }
    if (defaults.originType == 'fileset') {
        defaults.classes = 'fileset fsid' + defaults.fsID + ' fileset-' + selectedFilesets.indexOf(parseInt(defaults.fsID));
        defaults.inputValue = 'fsID' + defaults.fsID;
    } else {
        defaults.classes = '';
        defaults.inputValue = defaults.fID;
    }
    let newSlide;
    if ($element) {
        newSlide = $element.replaceWithPush(_templateSlide(defaults));
    } else {
        sliderEntriesContainer.append(_templateSlide(defaults));
        newSlide = $('.image-item').last();
    }
    if (!file) {
        attachFileManagerLaunch(newSlide);
        attachUploadEvent(newSlide);
    } else {
        attachDelete(newSlide);
        initImageEdit(newSlide,file);
        newSlide.find('.browse-file').remove();
        newSlide.find('.image-fID').val(defaults.inputValue);
    }
    newSlide.find('[data-field=entry-link-page-selector]').concretePageSelector({
        inputName: 'internal_link_cid[' + defaults.fID + '][]',
        cID: defaults.internal_link_cid,
    })
    refreshManager();
    return newSlide;
}

function refreshManager() {
    $('.image-item').not('.filled').appendTo(sliderEntriesContainer);
    sliderEntriesContainer.sortable({handle: '.handle'});
    const b = $('#easy_image_save');
    if(!$('.image-item.filled').length) {
        b.addClass('disabled');
    } else if (b.is('.disabled')) {
        b.removeClass('disabled');
    }
}

function addFileset(fsID) {
    if ($.inArray(fsID, selectedFilesets) > -1) {
        if (!window.confirm(<?= json_encode(t('This Fileset have been already picked, are you sure to add images again ?')) ?>)) {
            return;
        }
    } else {
        selectedFilesets.push(parseInt(fsID));
    }
    selectedFilesets = selectedFilesets.filter(function(itm, i, a) {
        return i == a.indexOf(itm);
    });
    $.post(
        <?= json_encode((string) $controller->getActionURL('getFileSetImages')) ?>,
        {
            <?= json_encode($token::DEFAULT_TOKEN_NAME) ?>: <?= json_encode($token->generate('eig_getFileSetImages')) ?>,
            fsID: fsID,
        },
        function(data) {
            if(data.length) {
                $.each(data,function(i,f){
                    fillTemplate(f);
                    refreshManager();
                });
            }
        },
        'json'
    );
}

function removeFileset(fsID) {
    fsID = parseInt(fsID);
    if ($.inArray(parseInt(fsID), selectedFilesets) === -1) {
        window.alert(<?= json_encode(t('Ouups the fileset has not been found here..')) ?>);
        return;
    }
    const _selectedFileset = [];
    for (let index = 0; index < selectedFilesets.length; ++index) {
        if(selectedFilesets[index] != fsID) {
            _selectedFileset.push(selectedFilesets[index]);
        }
    }
    selectedFilesets = _selectedFileset;
    $('.fsid' + fsID).remove();
}

function displayLinkChooser(container, type) {
    switch(type) {
        case 'URL':
            container.find('div[data-field=entry-link-page-selector]').hide();
            container.find('.entry-link-url').show();
            break;
        case 'Page':
            container.find('.entry-link-url').hide();
            container.find('div[data-field=entry-link-page-selector]').show();
            break;
        default:
            container.find('div[data-field=entry-link-page-selector]').hide();
            container.find('.entry-link-url').hide();
            break;
    }
}


$('#fsID').select2({
    width: 'copy',
});

$('#fsID').change(function(e) {
    const r = e.removed;
    const a = e.added;
    if (typeof r === 'object') {
        removeFileset(r.id);
    }
    if (typeof a === 'object') {
        addFileset(a.id);
    }
});

$('#options-button').on('click', function(e) {
    $('#advanced-options-content').slideUp();
    $('#options-content').slideToggle();
});
$('#advanced-options-button').on('click', function(e) {
    $('#options-content').slideUp();
    $('#advanced-options-content').slideToggle();
});
$('.easy_image_options_close').on('click', function(e) {
    $('.options-content').slideUp();
});

$('#easy_image_save').on('click', function () {
    $('#ccm-block-form').submit();
    ConcreteEvent.fire('EditModeExitInlineSaved');
    ConcreteEvent.fire('EditModeExitInline', {
        action: 'save_inline'
    });
});

$('#cancel-button').on('click', function() {
    ConcreteEvent.fire('EditModeExitInline');
    Concrete.getEditMode().scanBlocks();
});

<?php
foreach ($fDetails as $f) {
    ?>
    fillTemplate(<?= json_encode($f) ?>);
    <?php
}
?>
fillTemplate();

});
</script>
