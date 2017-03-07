# xMaps
> A platform for crowd-sourcing of geo-located cultural information.

The xMaps Platform (pending a change to a catchier name) builds on the
[ArtMaps Platform](https://github.com/horizon-institute/artmaps). It provides
a platform for creating geo-tagged content and faciliting discussion around that
content. In particular, it allows for new location information to be added
to the content by users that in turn can be used to drive the discussion. In
addition, the xMaps platform implements the 
[Wander/Anywhere](http://wanderanywhere.com/)
[API](http://wanderanywhere.com/api) so that content can be gathered in
collections that can be explored in the physical world through the use of
a mobile-web application.

## Installation

xMaps is implemented as a Wordpress plugin and theme. You must have a working
Wordpress instance in order to run xMaps. You must also be running a version
of MySQL greater than 5.5 (the plugin uses spatial indexes) with your Wordpress
install.

To install the plugin, create a new directory called xmaps in your Wordpress
plugin directory and copy all content from the ``src/plugin`` directory in this
repository to this new directory.

To install the theme, create a new directory called xmaps in your Wordpress
theme directory and copy all content from the ``src/theme`` directory in this
repository to this new directory. The theme is deliberately basic and it is
recommended that you clone the theme and modify it according to your needs, it
is however enough to get you up and running quickly.

Once installed and the plugin and theme are activated through the Wordpress
administrative interface, you will have an extra option on the *Settings* menu
called *xMaps*. Currently the only setting that is needed is a valid Google Maps
API key.

## Development setup

[Vagrant](https://www.vagrantup.com/) is used for development. To get started, 
copy the ``vagrant/config.rb.example`` file to ``vagrant/config.rb`` and
edit the contents to your own needs. At present, the only configuration
setting is the IP address, the Vagrant box uses this as a static IP so that
the server can easily be found via this IP. Once ``vagrant up`` has completed,
you can find the xMaps development install at that IP address. See the
*Installation* section above for setting required configuration values. As
the source folder is mounted directly inside the Vagrant box, all changes to
source files will be immediately visible on the development Wordpress instance
inside the box.

## Meta

Distributed under the AGPLv3 license. See ``LICENSE`` for more information.

[https://github.com/horizon-institute/xmaps]()
