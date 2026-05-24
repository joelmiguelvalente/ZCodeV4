<picture class="picture overflow-hidden">
    {if !empty($src) && is_array($src)}

        {if !empty($src['lg'])}
            <source srcset="{$tsRoutes['logos']['x256']}" data-srcset="{$src['lg']|default:''}" media="(min-width: 1200px)">
        {/if}

        {if !empty($src['md'])}
            <source srcset="{$tsRoutes['logos']['x128']}" data-srcset="{$src['md']|default:''}" media="(min-width: 800px)">
        {/if}

        {if !empty($src['sm'])}
            <source srcset="{$tsRoutes['logos']['x64']}" data-srcset="{$src['sm']|default:''}" media="(min-width: 400px)">
        {/if}

    {/if}

    {include "Avatar.tpl" src=is_array($src) ? ($src['md']|default:$src['sm']|default:$src['lg']|default:'') : $src alt=$alt|default:'' class=$class|default:''}
</picture>

