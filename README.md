MDViewer
============
### Description
Display Markdown-Text from an external source such as Github-Repos.

With version >=1.4.0 of this plugin, external sources can contain blocks that follow a specific syntax which is similar to the one known from `ilTemplate`. An example can be found below.
```
[//]: # (BEGIN block_name_1)
...
### My Block title
Lorem ipsdum dolor sit amet ...
...
[//]: # (END block_name_1)
```
As you can see, the block "tags" are official markdown-comments which are not visible when rendering the actual MD file.

Of course, `block_name_1` can be replaced by the name of your choosing and wrap any content you like. Note that there is no duplicate protection though, so if multiple blocks with the same name are found, the first one will be used.

### Installation
Start at your ILIAS root directory
```bash
mkdir -p Customizing/global/plugins/Services/COPage/PageComponent/
cd Customizing/global/plugins/Services/COPage/PageComponent
git clone https://github.com/srsolutionsag/MDViewer.git
```
As ILIAS administrator go to "Administration->Plugins" and install/activate the plugin.

# ILIAS Plugin SLA
We love and live the philosophy of Open Source Software! Most of our developments, which we develop on behalf of customers or in our own work, we make publicly available to all interested parties free of charge at https://github.com/srsolutionsag.

Do you use one of our plugins professionally? Secure the timely availability of this plugin also for future ILIAS versions by signing an SLA. Find out more about this at https://sr.solutions/plugins.

Please note that we only guarantee support and release maintenance for institutions that sign an SLA.

### Contact
info@sr.solutions
https://sr.solutions.ch  
