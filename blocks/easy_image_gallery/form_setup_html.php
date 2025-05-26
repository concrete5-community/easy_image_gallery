<?php  defined('C5_EXECUTE') or die("Access Denied.");
$fp = FilePermissions::getGlobal();
$tp = new TaskPermission();
?>
<ul id="" class="ccm-inline-toolbar ccm-ui easy-image-toolbar">
    <li class="ccm-sub-toolbar-text-cell">
        <?php if(count($fileSets)) : ?>
        <label for="fsID"><?php echo t("Add a Filset:")?></label>
        <select name="fsID" multiple id="fsID" style="width:300px" data-placeholder="<?php echo t('Choose') ?>">
            <?php foreach ($fileSets as $key => $fs) :?>
            <option value="<?php echo $fs->getFileSetID() ?>" <?php echo in_array($fs->getFileSetID(),$selectedFilesets) ? 'selected' : '' ?>><?php echo $fs->getFileSetName() ?></option>
            <?php endforeach; ?>
        </select>
        <?php else: ?>
        <label for="fsID"><?php echo t("No Filset")?></label>
        <?php endif ?>
    </li>
    <li class="ccm-inline-toolbar-button ccm-inline-toolbar-button-options">
        <button id="options-button" type="button" class="btn btn-mini"><?php echo t("Options")?></button>
    </li>
    <li class="ccm-inline-toolbar-button ccm-inline-toolbar-button-options">
        <button id="advanced-options-button" type="button" class="btn btn-mini"><?php echo t("Adv Opt")?></button>
    </li>
    <li class="ccm-inline-toolbar-button ccm-inline-toolbar-button-sort">
        <button id="sort-button" type="button" class="btn btn-mini"><?php echo t("Sort")?></button>
    </li>
    <li class="ccm-inline-toolbar-button ccm-inline-toolbar-button-cancel">
        <button onclick="cancelBlockForm()" id="" type="button" class="btn btn-mini"><?php echo t("Cancel")?></button>
    </li>
    <?php if(!$isComposer): ?>
    <li class="ccm-inline-toolbar-button ccm-inline-toolbar-button-save">
      <button onclick="submitBlockForm()" class="btn btn-primary" type="button" id="easy_image_save"><?php if ($controller->getTask() == 'add') { ?><?php echo t('Add Gallery')?><?php } else { ?><?php echo t('Update Gallery')?><?php } ?></button>
    </li>
    <?php endif ?>
 </ul>

<?php $this->inc('advanced_options.php', array('view' => $view, 'options' => $controller->getOptionsJson(), 'form' => $form)); ?>

<div class="basic-image-form-wrapper ccm-ui">
    <div class="easy_image-items"></div>
</div>

<script type="text/template" id="imageTemplate">
    <div class="image-item block-to-sort <% if (image_url.length > 0) { %>filled fid-<%= fID %> <% } %> ccm-ui <%= classes %>" <% if (originType == 'fileset') { %>rel="<%=filesetName%>" <% } %>>
        <div id="manage-file" class="manage-file">
            <% if (image_url.length > 0) { %>
            <div class="img" style="background-image:url(<%= image_url %>)"></div>
            <div class="item-toolbar">
                <h4 data-type="textarea" data-name="fvTitle"  class="editable editable-click" title="<?php echo t('Title') ?>"><%= title %></h4>
                <p><strong><?php echo t('Description : ') ?></strong><span class="description editable editable-click" data-placeholder="<?php echo t('Write your description') ?>" data-name="fvDescription" data-type="textarea" <% if (!description) { %> editable-empty <% } %>><%= description %></span></p>

                <hr class="separator">

                <p><strong><?php echo t('Link type') ?> : </strong><span class="link_type editable editable-click" data-placeholder="<?php echo t('Link type') ?>" data-value="<%= link_type %>" data-name="link_type" data-type="select" data-source='{"None": "None", "URL":"External URL", "Page": "Link to page"}' <% if (!link_type) { %> editable-empty <% } %>><%= link_type %></span></p>
                <p class="entry-link-url" style="<% if (link_type != 'URL') { %> display: none;<% } %>"><strong><?php echo t('External URL') ?> : </strong><span data-field="entry-link-url"  data-type="textarea" data-name="external_link_url"  class="editable editable-click" data-placeholder="<?php echo t('http://') ?>" title="<?php echo t('External URL') ?>"><%=external_link_url%></span></p>
                <div style="<% if (link_type != 'Page') { %> display: none;<% } %>" data-field="entry-link-page-selector" class="form-group">
                   <label><?php echo t('Choose Page:') ?></label>
                    <div data-field="entry-link-page-selector-select"></div>
                </div>

                <a href="javascript:;" class="remove-item"><i class="fa fa-remove"></i></a>
                <div class="item-controls">
                    <a class="dialog-launch item-properties" dialog-modal="true" dialog-width="600" dialog-height="400" dialog-title="Properties" href="<?php echo URL::to('/ccm/system/dialogs/file/properties') ?>?fID=<%= fID %>"><i class="fa fa-gear"></i></a>
                    <a class="handle"><i class="fa fa-arrows"></i></a>
                </div>
            </div>
            <input type="hidden" name="<?php echo $view->field('fID')?>[]" class="image-fID" value="<%=inputValue%>" />
            <input type="hidden" name="<?php echo $view->field('uniqueFID')?>[]" class="unique-image-fID" value="<%=fID%>" />
            <% } else { %>
            <div class="add-file-control block-to-sort">
                <a href="#" class="upload-file"><i class="fa fa-upload"></i></a><a href="#" class="add-file"><i class="fa fa-th-list"></i></a>
                <h4 style="display:none">zzzz</h4>
            </div>
            <span class="process"><?php echo t('Processing') ?> <i class="fa fa-cog fa-spin"></i></span>
            <input type="text" class="knob" value="0" data-width="150" data-height="150" data-fgColor="#555" data-readOnly="1" data-bgColor="#e1e1e1" data-thickness=".1" />
            <input type="file" name="files[]" class="browse-file" multiple />
            <% } %>
        </div>
    </div>
</script>

<script>
    var CCM_EDITOR_SECURITY_TOKEN = "<?php echo Loader::helper('validation/token')->generate('editor')?>";
    var getFileDetailDetailJson = '<?php echo URL::to("/easyimagegallery/tools/getfiledetailsjson")?>';
    var saveFieldURL = '<?php echo URL::to("/easyimagegallery/tools/savefield")?>';
    var getFilesetImagesURL = '<?php echo URL::to("/easyimagegallery/tools/getfilesetimages")?>';
    var selectedFilesets = <?php echo $selectedFilesets ? json_encode($selectedFilesets) : 'new Array()'  ?>;

    easy_image_manager ($('.easy_image-items'));


    ccmi18n.filesetAlreadyPicked = "<?php echo t('This Fileset have been already picked, are you sure to add images again ?') ?>";
    ccmi18n.filesetNotFound = "<?php echo t('Ouups the fileset has not been found here..') ?>";
    ccmi18n.confirmDeleteImage = "<?php echo t('Are you sure to delete this image?') ?>";
    ccmi18n.imageOnly = "<?php echo t('You must select an image file only'); ?>";
    ccmi18n.imageSize = "<?php echo t('Please upload a smaller image, max size is 6 MB') ?>";


    $(document).ready(function(){
      $('#fsID').select2({
        width:'copy'
      });

        <?php if (is_array($fDetails) && count($fDetails)) : ?>
            <?php foreach ($fDetails as $key => $f) : ?>
        fillTemplate(<?php echo json_encode($f) ?>);
            <?php endforeach ?>
        <?php endif ?>
    });
</script>
