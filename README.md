MDViewer
============
### Description
Display Markdown-Text from an external source such as Github-Repos.

With version >=1.4.0 of this plugin, external sources can contain blocks that follow a specific syntax which is similar to the one known from `ilTemplate`. An example can be found below.
```
<!-- BEGIN block_name_1 -->
...
### My Block title
Lorem ipsdum dolor sit amet ...
...
<!-- END block_name_1 -->
```
As you can see, the block "tags" are simple HTML-comments which are not visible when rendering the actual MD file.

Of course, `block_name_1` can be replaced by the name of your choosing and wrap any content you like. Note that there is no duplicate protection though, so if multiple blocks with the same name are found, the first one will be used.

### Installation
Start at your ILIAS root directory
```bash
mkdir -p Customizing/global/plugins/Services/COPage/PageComponent/
cd Customizing/global/plugins/Services/COPage/PageComponent
git clone https://github.com/studer-raimann/MDViewer.git
```
As ILIAS administrator go to "Administration->Plugins" and install/activate the plugin.

### ILIAS Plugin SLA

Wir lieben und leben die Philosophie von Open Source Software! Die meisten unserer Entwicklungen, welche wir im Kundenauftrag oder in Eigenleistung entwickeln, stellen wir öffentlich allen Interessierten kostenlos unter https://github.com/studer-raimann zur Verfügung.

Setzen Sie eines unserer Plugins professionell ein? Sichern Sie sich mittels SLA die termingerechte Verfügbarkeit dieses Plugins auch für die kommenden ILIAS Versionen. Informieren Sie sich hierzu unter https://studer-raimann.ch/produkte/ilias-plugins/plugin-sla.

Bitte beachten Sie, dass wir nur Institutionen, welche ein SLA abschliessen Unterstützung und Release-Pflege garantieren.

### Contact
info@studer-raimann.ch  
https://studer-raimann.ch  
