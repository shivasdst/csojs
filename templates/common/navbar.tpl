{**
 * templates/common/navbar.tpl
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Navigation Bar
 *
 *}
<div id="navbar">
	<ul class="menu">
		<li id="home"><a href="{url page="index"}">{translate key="navigation.home"}</a></li>
		<li id="about"><a href="{url page="about"}">{translate key="navigation.about"}</a></li>

		{if $isUserLoggedIn}
			<li id="userHome"><a href="{url journal="index" page="user"}">{translate key="navigation.userHome"}</a></li>
			{if $Roles_array}
				{foreach from=$Roles_array item=role}
					{if $role == "user.role.author"}
						<li id="authorFaqs"><a href="http://localhost/csojs/index.php/cs/pages/view/faqs">FAQs</a></li>
					{/if}
				{/foreach}
			{/if}
		{else}
			<li id="login"><a href="{url page="login"}">{translate key="navigation.login"}</a></li>
			{if !$hideRegisterLink}
				<li id="register"><a href="{url page="user" op="register"}">{translate key="navigation.register"}</a></li>
			{/if}
		{/if}{* $isUserLoggedIn *}

		{if $siteCategoriesEnabled}
			<li id="categories"><a href="{url journal="index" page="search" op="categories"}">{translate key="navigation.categories"}</a></li>
		{/if}{* $categoriesEnabled *}

		{if !$currentJournal || $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
			<li id="search"><a href="{url page="search"}">{translate key="navigation.search"}</a></li>
		{/if}

		{if $currentJournal && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
			<li id="current"><a href="{url page="issue" op="current"}">{translate key="navigation.current"}</a></li>
			<li id="archives"><a href="{url page="issue" op="archive"}">{translate key="navigation.archives"}</a></li>
		{/if}

		{if $enableAnnouncements}
			<li id="announcements"><a href="{url page="announcement"}">{translate key="announcement.announcements"}</a></li>
		{/if}{* enableAnnouncements *}

		{call_hook name="Templates::Common::Header::Navbar::CurrentJournal"}

		{foreach from=$navMenuItems item=navItem key=navItemKey}
			{if $navItem.url != '' && $navItem.name != ''}
				<li class="navItem" id="navItem-{$navItemKey|escape}"><a href="{if $navItem.isAbsolute}{$navItem.url|escape}{else}{$baseUrl}{$navItem.url|escape}{/if}">{if $navItem.isLiteral}{$navItem.name|escape}{else}{translate key=$navItem.name}{/if}</a></li>
			{/if}
		{/foreach}
	</ul>
</div>

