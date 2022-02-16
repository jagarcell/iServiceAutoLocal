# iServiceAuto Case Study
On this file you will be able to find the instructions as to how to setup the application.  

Minimum Requirements: 

    • Vagrant 2.2.19 (Latest released version) 
    • VirtualBox 6.1.32 r149290 (Qt5.6.2)
    
Please refer to the following sites on how to install each one of the techonologies mentioned above:

    • Vagrant: https://www.vagrantup.com/downloads.html
    • VirtualBox: https://www.virtualbox.org/wiki/Downloads

    1. Navigate in your browser to this repository https://github.com/jagarcell/iServiceAutoLocal.
       And find the file named Assessment.rar in the repostory's root, click on it to open the download page.
    2. Download it into your local machine to any folder that you like to and
       once the file has been downloaded, navigate to that folder and extract the file Assessment.rar using the 'extract here' option.
    3. Open a terminal and 'cd' to the extraction's resulting folder, should be 'Assessment'.
    4. Run 'vagrant up –provision' and wait until the 'Virtual Machine' is created in the VirtualBox and fully booted,the machine name in the VirtualBox is going to be          Assessment_JG.
    5. When the booting script in the terminal had ended run 'vagrant ssh', now you should be logged into the 'Virtual Machine' via ssh.
    6. In the 'Virtual Machine' 'cd' to 'code':
       vagrant@iserviceautolocal:~$ cd code.
       and execute 'bash after.sh', this will setup the Database:
       vagrant@iserviceautolocal:~/code$ bash after.sh.
       You will get the following message:
       Created database `iserviceauto` for connection named default.
       For the following prompt type 'yes' and hit ENTER:
        WARNING! You are about to execute a migration in database "iserviceauto" that could result in schema changes and data loss. Are you sure you wish to continue?          (yes/no) [yes]:
        >yes <enter>
       
       [notice] Migrating up to DoctrineMigrations\Version20220212135618
       [notice] finished in 306.4ms, used 14M memory, 1 migrations executed, 1 sql queries
       
       For this prompt as well type 'yes' and hit ENTER:
        Careful, database "iserviceauto" will be purged. Do you want to continue? (yes/no) [no]:
        >yes <enter>
       
          > purging database
          > loading App\DataFixtures\AppFixtures
       vagrant@iserviceautolocal:~/code$

You are all set! You can start testing the API sending the requests to http://127.0.0.1:8000

Known (possible) issues:
In some enviroments, when the Virtual Machine booting script performed by Vagrant finishes, you may notice an slow ssh login and API requests failures. This issue is due to the fact that even when the script has ended the job the machine is still starting services. You can check the progress of the machine by opening the 'Oracle VirtualBox Graphical User Interface' and selecting the Assessment_JG machine, the display emulator shows the machine activity. In any case it should not take more than a few minutes to fully boot up for the first time.    
