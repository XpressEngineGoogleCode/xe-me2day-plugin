<?php
if (!defined('__XE__')) exit();

/**
 * @brief me2day social plugin addon
 * @author NHN (developers@xpressengine.com)
 **/

if ($called_position == 'before_display_content' && Context::getResponseMethod() == 'HTML')
{
	if (!$addon_info->plugin_key) return;

	Context::loadFile('http://static.plugin.me2day.com/js/plugins_v1.js');

	// Find document
	$result = preg_match_all('/<!--BeforeDocument\(([0-9]+),[0-9]+\)-->/', $output, $matches);
	
	if (!$result) return;

	foreach($matches[1] as $documentSrl)
	{
		// Get document info
		$oDocumentModel = &getModel('document');
		$oDocument = $oDocumentModel->getDocument($documentSrl, false, false, array('title'));
		if (!$oDocument) return;

		// Make meta data
		$shortTitle = cut_str($oDocument->getTitleText(), 149 - strlen($addon_info->post_body));
		$url = $oDocument->getPermanentUrl();
		$linkedTitle = sprintf('"%s":%s ', $shortTitle, $url);

		if (!$addon_info->post_body)
		{
			$addon_info->post_body = '%link%';
		}
		$postBody = str_replace('%link%', $linkedTitle, $addon_info->post_body);
		$postTag = $addon_info->post_tag;

		// If there are one more document in page, skip set meta data
		if ($result == 1)
		{
			Context::addHtmlHeader(sprintf('<meta property="me2:post_body" content="%s" />', htmlspecialchars($postBody)));
			if ($postTag)
			{
				Context::addHtmlHeader(sprintf('<meta property="me2:post_tag" content="%s" />', htmlspecialchars($postTag)));
			}
		}

		// Make button html
		$buttonLayout = $addon_info->button_layout;
		if (!$buttonLayout) $buttonLayout = 'small';
		$buttonProfileImages= $addon_info->button_profile_images;
		if (!$buttonProfileImages) $buttonProfileImages= 'off';
		$buttonColor = $addon_info->button_color;
		if (!$buttonColor) $buttonColor = 'dark';
		$buttonPingback = $addon_info->button_pingback;
		if (!$buttonPingback) $buttonPingback = 'unchecked';
		
		$buttonHtml = sprintf('<me2:metoo layout="%s" profile_images="%s" color="%s" pingback="%s" href="%s" plugin_key="%s"></me2:metoo>',
				htmlspecialchars($buttonLayout),
				htmlspecialchars($buttonProfileImages),
				htmlspecialchars($buttonColor),
				htmlspecialchars($buttonPingback),
				htmlspecialchars($url),
				htmlspecialchars($addon_info->plugin_key)
		);

		if (!$addon_info->button_align || $addon_info->button_align == 'left')
		{
			$buttonHtml = sprintf('<div class="me2plugin_button" style="text-align: left;">%s</div>', $buttonHtml);
		}
		else if ($addon_info->button_align == 'center')
		{
			$buttonHtml = sprintf('<div class="me2plugin_button" style="text-align: center;">%s</div>', $buttonHtml);
		}
		else
		{
			$buttonHtml = sprintf('<div class="me2plugin_button" style="text-align: right;">%s</div>', $buttonHtml);
		}

		// Apply button html
		if (!$addon_info->button_position || $addon_info->button_position == 'top')
		{
			$output = preg_replace('/<!--BeforeDocument\('.$oDocument->get('document_srl').',[0-9]+\)-->/', "$0 $buttonHtml", $output);
		}
		else
		{
			$output = preg_replace('/<!--AfterDocument\('.$oDocument->get('document_srl').',[0-9]+\)-->/', "$buttonHtml $0", $output);
		}

		if ($addon_info->comment_use != 'Y') return;

		// Make comment html
		$commentWidth = $addon_info->comment_width;
		if (!$commentWidth) $commentWidth = 550;
		$commentCount = $addon_info->comment_count;
		if (!$commentCount) $commentCount = 5;
		$commentColor = $addon_info->comment_color;
		if (!$commentColor) $commentColor = 'dark';
		$commentPingback = $addon_info->comment_pingback;
		if (!$commentPingback) $commentPingback = 'unchecked';

		$commentHtml = sprintf('<me2:comment count="%s" width="%s" color="%s" pingback="%s" href="%s" plugin_key="%s"></me2:comment>',
				$commentCount,
				$commentWidth,
				$commentColor,
				$commentPingback,
				$url,
				$addon_info->plugin_key
		);
		
		// Apply button html
		$output = preg_replace('/<!--AfterDocument\('.$oDocument->get('document_srl').',[0-9]+\)-->/', "$commentHtml $0", $output);
	}
}

/* End of file me2plugin.addon.php */
/* Location: ./addons/me2plugin/me2plugin.addon.php */
