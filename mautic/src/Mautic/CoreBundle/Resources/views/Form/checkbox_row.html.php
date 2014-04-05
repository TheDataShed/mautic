<?php $feedbackClass = ($app->getRequest()->getMethod() == 'POST' && !empty($errors)) ? " has-error has-feedback" : ""; ?>
<div class="row">
    <div class="form-group col-xs-12 col-md-8 col-lg-6<?php echo $feedbackClass; ?>">
        <div class="checkbox">
            <label>
                <?php echo $view['form']->widget($form); ?>
                <?php echo $view['translator']->trans($form->vars['label']); ?>
            </label>
        </div>
        <?php echo $view['form']->errors($form) ?>
    </div>
</div>