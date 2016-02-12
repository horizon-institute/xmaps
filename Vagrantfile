# -*- mode: ruby -*-
# vi: set ft=ruby :

require "./vagrant/config.rb"

Vagrant.configure(2) do |config|
  
  config.vm.box = "ubuntu/trusty64"
  
  config.vm.network "private_network", ip: DevEnv::IP
  
  config.vm.provider "virtualbox" do |v|
    v.memory = 1024 # MySQL requires 1GiB minimum
  end
  
  config.vm.provision "shell" do |s|
  	s.path = "vagrant/provision.sh"
  	s.args = [ DevEnv::IP ]
  end  
  
end
