<div class="row<?php echo $form->vars['attr']['class']; ?>" id="permissions-container">
    <div class="form-group col-xs-12 col-md-8 col-lg-6 role-permissions">
        <label><?php echo $view['translator']->trans($form->vars['label']); ?></label>
        <?php echo $view['form']->errors($form) ?>
            <?php if (!empty($form->children)): ?>
                <?php foreach ($form->children as $child): ?>
                    <?php echo $view['form']->row($child); ?>
                <?php endforeach; ?>
            <?php endif; ?>
    </div>
</div>