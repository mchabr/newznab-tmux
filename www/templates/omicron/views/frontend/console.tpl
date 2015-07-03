<div class="header">
	{assign var="catsplit" value=">"|explode:$catname}
	<h2>{$catsplit[0]} > <strong>{$catsplit[1]}</strong></h2>

	<div class="breadcrumb-wrapper">
		<ol class="breadcrumb">
			<li><a href="{$smarty.const.WWW_TOP}{$site->home_link}">Home</a></li>
			/ {$catname|escape:"htmlall"}
		</ol>
	</div>
</div>
<div class="box-body">
<div class="row">
	<div class="col-xlg-12 portlets">
		<div class="panel">
			<div class="panel-content pagination2">
				<div class="row">
					<div class="col-md-8">
						<form id="nzb_multi_operations_form" action="get" style="width: inherit">
							<div class="nzb_multi_operations">
								View: <strong>Covers</strong> | <a
										href="{$smarty.const.WWW_TOP}/browse?t={$category}">List</a><br/>
								Check all: <input type="checkbox" class="fa fa-check-square-o nntmux_check_all"/> <br/>
								With Selected:
								<div class="btn-group">
									<input type="button"
										   class="nntmux_multi_operations_download btn btn-sm btn-success"
										   value="Download NZBs"/>
									<input type="button"
										   class="nntmux_multi_operations_cart btn btn-sm btn-info"
										   value="Add to Cart"/>
									{if isset($sabintegrated)}
										<input type="button"
											   class="nntmux_multi_operations_sab btn btn-sm btn-primary"
											   value="Send to Queue"/>
									{/if}
									{if isset($nzbgetintegrated)}
										<input type="button"
											   class="nntmux_multi_operations_nzbget btn btn-sm btn-primary"
											   value="Send to NZBGet"/>
									{/if}
									{if isset($isadmin)}
										<input type="button"
											   class="nntmux_multi_operations_delete btn btn-sm btn-danger"
											   value="Delete"/>
									{/if}
								</div>
							</div>
						</form>
					</div>
					<div class="col-md-4">
						{$pager}
					</div>
				</div>
				{foreach from=$results item=result}
					{assign var="msplits" value=","|explode:$result.grp_release_id}
					{assign var="mguid" value=","|explode:$result.grp_release_guid}
					{assign var="mnfo" value=","|explode:$result.grp_release_nfoid}
					{assign var="mgrp" value=","|explode:$result.grp_release_grpname}
					{assign var="mname" value="#"|explode:$result.grp_release_name}
					{assign var="mpostdate" value=","|explode:$result.grp_release_postdate}
					{assign var="msize" value=","|explode:$result.grp_release_size}
					{assign var="mtotalparts" value=","|explode:$result.grp_release_totalparts}
					{assign var="mcomments" value=","|explode:$result.grp_release_comments}
					{assign var="mgrabs" value=","|explode:$result.grp_release_grabs}
					{assign var="mpass" value=","|explode:$result.grp_release_password}
					{assign var="minnerfiles" value=","|explode:$result.grp_rarinnerfilecount}
					{assign var="mhaspreview" value=","|explode:$result.grp_haspreview}
					{foreach from=$msplits item=m name=loop}
						{if $smarty.foreach.loop.first}
							<div class="panel">
								<div class="panel-content">
									<div class="row">
										<div class="col-md-2 no-gutter">
											<a title="View details"
											   href="{$smarty.const.WWW_TOP}/details/{$mguid[$m@index]}/{$mname[$m@index]|escape:"seourl"}">
												<img src="{$smarty.const.WWW_TOP}/covers/console/{if $result.cover == 1}{$result.consoleinfoid}.jpg{else}no-cover.jpg{/if}"
													 width="140" border="0"
													 alt="{$result.title|escape:"htmlall"}"/>
											</a>
											{if $result.url != ""}<a class="label label-default"
																	 target="_blank"
																	 href="{$site->dereferrer_link}{$result.url}"
																	 name="amazon{$result.consoleinfoid}"
																	 title="View Amazon page">
													Amazon</a>{/if}
											{if $result.nfoid > 0}<a
												href="{$smarty.const.WWW_TOP}/nfo/{$mguid[$m@index]}/{$mname[$m@index]|escape:"seourl"}"
												title="View NFO" class="label label-default" rel="nfo">
													NFO</a>{/if}
											<a class="label label-default"
											   href="{$smarty.const.WWW_TOP}/browse?g={$result.group_name}"
											   title="Browse releases in {$result.group_name|replace:"alt.binaries":"a.b"}">Group</a>
										</div>
										<div class="col-md-10 no-gutter">
											<h4><a title="View details"
												   href="{$smarty.const.WWW_TOP}/details/{$mguid[$m@index]}/{$mname[$m@index]|escape:"seourl"}">{$result.title|escape:"htmlall"}</a>
											</h4>
											<table>
												<tr>
													<td>
														<input type="checkbox" class="fa fa-check-square-o nzb_check"
															   value="{$mguid[$m@index]}" id="chksingle"/>
														<span class="label label-default">{$msize[$m@index]|fsize_format:"MB"}</span>
																	<span class="label label-default">Posted {$mpostdate[$m@index]|timeago}
																		ago</span>
														{if isset($isadmin)}<a class="label label-warning"
																			   href="{$smarty.const.WWW_TOP}/admin/release-edit.php?id={$result.grp_release_id}&amp;from={$smarty.server.REQUEST_URI}"
																			   title="Edit release">
																Edit</a>{/if}
														<br/>
														{if $result.genre != ""}
															<b>Genre:</b>
															{$result.genre}
															<br/>
														{/if}
														{if $result.esrb != ""}
															<b>Rating:</b>
															{$result.esrb}
															<br/>
														{/if}
														{if $result.publisher != ""}
															<b>Publisher:</b>
															{$result.publisher}
															<br/>
														{/if}
														{if $result.releasedate != ""}
															<b>Released:</b>
															{$result.releasedate|date_format}
															<br/>
														{/if}
														{if $result.review != ""}
															<b>Review:</b>
															{$result.review|escape:'htmlall'}
															<br/>
														{/if}
														<div>
															<a role="button" class="btn btn-white btn-xs"
															   href="{$smarty.const.WWW_TOP}/getnzb/{$mguid[$m@index]}/{$mname[$m@index]|escape:"url"}"><i
																		class="fa fa-download"></i><span
																		class="badge">{$mgrabs[$m@index]}
																	Grab{if $mgrabs[$m@index] != 1}s{/if}</span></a>
															<a role="button" class="btn btn-white btn-xs"
															   href="{$smarty.const.WWW_TOP}/details/{$mguid[$m@index]}/{$mname[$m@index]|escape:"seourl"}#comments"><i
																		class="fa fa-comment-o"></i><span
																		class="badge">{$mcomments[$m@index]}
																	Comment{if $mcomments[$m@index] != 1}s{/if}</span></a>
														</div>
													</td>
												</tr>
											</table>
										</div>
									</div>
								</div>
							</div>
						{/if}
					{/foreach}
				{/foreach}
				<div class="row">
					<div class="col-md-8">
						<form id="nzb_multi_operations_form" action="get">
							<div class="nzb_multi_operations">
								View: <strong>Covers</strong> | <a
										href="{$smarty.const.WWW_TOP}/browse?t={$category}">List</a><br/>
								Check all: <input type="checkbox" class="fa fa-check-square-o nntmux_check_all"/> <br/>
								With Selected:
								<div class="btn-group">
									<input type="button"
										   class="nntmux_multi_operations_download btn btn-sm btn-success"
										   value="Download NZBs"/>
									<input type="button"
										   class="nntmux_multi_operations_cart btn btn-sm btn-info"
										   value="Add to Cart"/>
									{if isset($sabintegrated)}
										<input type="button"
											   class="nntmux_multi_operations_sab btn btn-sm btn-primary"
											   value="Send to Queue"/>
									{/if}
									{if isset($nzbgetintegrated)}
										<input type="button"
											   class="nntmux_multi_operations_nzbget btn btn-sm btn-primary"
											   value="Send to NZBGet"/>
									{/if}
									{if isset($isadmin)}
										<input type="button"
											   class="nntmux_multi_operations_delete btn btn-sm btn-danger"
											   value="Delete"/>
									{/if}
								</div>
							</div>
						</form>
					</div>
					<div class="col-md-4">
						{$pager}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>