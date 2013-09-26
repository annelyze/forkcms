{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_MODULES_PATH}/pages/layout/templates/structure_start.tpl}

{form:add}
	<div class="pageTitle">
		<h2>{$lblPages|ucfirst}: {$lblAdd}</h2>

		{option:showPagesIndex}
			<div class="buttonHolderRight">
				<a href="{$var|geturl:'index'}" class="button icon iconBack"><span>{$lblOverview|ucfirst}</span></a>
			</div>
		{/option:showPagesIndex}
	</div>

	<p id="pagesPageTitle">
		<label for="title">{$lblTitle|ucfirst}</label>
		{$txtTitle} {$txtTitleError}
		<span class="oneLiner">
			<span><a href="{$SITE_URL}">{$SITE_URL}{$prefixURL}/<span id="generatedUrl"></span></a></span>
		</span>
	</p>

	{* Buttons to save page *}
	<div id="pageButtons" class="fullwidthOptions">
		<div class="buttonHolderRight">
			<input id="saveAsDraft" class="button mainButton" type="submit" name="add" value="{$lblNext|ucfirst}" />
		</div>
	</div>
{/form:add}

{include:{$BACKEND_MODULES_PATH}/pages/layout/templates/structure_end.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}
