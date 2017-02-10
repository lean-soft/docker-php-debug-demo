// ----------------------------------------------------------------------------
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
mySettings = {
	markupSet: [
		{name:'[b]', key:'B', openWith:'[b]', closeWith:'[/b]'},
		{name:'[i]', key:'I', openWith:'[i]', closeWith:'[/i]'},
		{name:'[u]', key:'U', openWith:'[u]', closeWith:'[/u]'},
		{separator:'---------------' },
		{name:'[left]', openWith:'[left]', closeWith:'[/left]'},
		{name:'[center]', openWith:'[center]', closeWith:'[/center]'},
		{name:'[right]', openWith:'[right]', closeWith:'[/right]'},
		{name:'[justify]', openWith:'[justify]', closeWith:'[/justify]'},
		{separator:'---------------' },
		{name:'[list]', key:'L', openWith:'[list]\n', closeWith:'\n[/list]'},
		{name:'[*]', openWith:'[*]'},
		{separator:'---------------' },
		{	name:'[color]', 
			className:'colors', 
			openWith:'[color=]', 
			closeWith:'[/color]', 
				dropMenu: [
					{name:'Yellow',	openWith:'[color=yellow]', 	closeWith:'[/color]', className:"col1-1" },
					{name:'Orange',	openWith:'[color=orange]', 	closeWith:'[/color]', className:"col1-2" },
					{name:'Red', 	openWith:'[color=red]', 	closeWith:'[/color]', className:"col1-3" },
					
					{name:'Blue', 	openWith:'[color=blue]', 	closeWith:'[/color]', className:"col2-1" },
					{name:'Purple', openWith:'[color=purple]', 	closeWith:'[/color]', className:"col2-2" },
					{name:'Green', 	openWith:'[color=green]', 	closeWith:'[/color]', className:"col2-3" },
					
					{name:'White', 	openWith:'[color=white]', 	closeWith:'[/color]', className:"col3-1" },
					{name:'Gray', 	openWith:'[color=gray]', 	closeWith:'[/color]', className:"col3-2" },
					{name:'Black',	openWith:'[color=black]', 	closeWith:'[/color]', className:"col3-3" }
				]
		},
		{separator:'---------------' },
		{name:'[img]', key:'P', openWith:'[img]', closeWith:'[/img]'},
		{name:'[url]', key:'L', openWith:'[url]', closeWith:'[/url]'},
		{name:'[email]', key:'E', openWith:'[email]', closeWith:'[/email]'},
		{name:'[video]', openWith:'[video]', closeWith:'[/video]'},
		{separator:'---------------' },
		{name:'[quote]', key:'Q', openWith:'[quote]', closeWith:'[/quote]'},
		{name:'[code]', openWith:'[code]', closeWith:'[/code]', 
		dropMenu :[
			{name:'Text', openWith:'[code=generic]', closeWith:'[/code]' },
			{name:'CSS', openWith:'[code=css]', closeWith:'[/code]' },
			{name:'HTML', openWith:'[code=html]', closeWith:'[/code]' },
			{name:'Java', openWith:'[code=java]', closeWith:'[/code]' },
			{name:'JavaScript', openWith:'[code=javascript]', closeWith:'[/code]' },
			{name:'Perl', openWith:'[code=perl]', closeWith:'[/code]' },
			{name:'PHP', openWith:'[code=php]', closeWith:'[/code]' },
			{name:'Ruby', openWith:'[code=ruby]', closeWith:'[/code]' },
			{name:'SQL', openWith:'[code=sql]', closeWith:'[/code]' },
			{name:'VB', openWith:'[code=vbscript]', closeWith:'[/code]' },
			{name:'XLS', openWith:'[code=xls]', closeWith:'[/code]' }
		]}
	]
}