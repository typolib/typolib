
<form name="addform" id="mainform" method="get" action="">
    <fieldset id="main">

        <?php if (isset($code_selector)) : ?>
        <fieldset>
            <label>Code</label>
            <select name="code" title="Code" id="code_selector">
            <?=$code_selector?>
            </select>
        </fieldset>
        <?php endif; ?>

        <?php if (isset($ruletypes_selector)) : ?>
        <fieldset>
            <label>Rule type</label>
            <select name="type" title="Rule type" id="addrule_type">
            <?=$ruletypes_selector?>
            </select>
        </fieldset>
        <?php endif; ?>

        <br/>
        <fieldset>
            <p>Enter a new rule:<br />
            <input type="text" name="rule"/></p>
        </fieldset>
        <br/>
        <input type="submit" value="Add" alt="Add" />
    </fieldset>
</form>
