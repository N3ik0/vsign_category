{foreach from=$categoriesDetails item=category}
    <a href="{$category.link|escape:'html':'UTF-8'}" class="btn btn-primary">
        {$category.name|escape:'html':'UTF-8'}
    </a>
{/foreach}
