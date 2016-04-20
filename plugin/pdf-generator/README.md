This bundle requires wkhtmltopdf (http://wkhtmltopdf.org/downloads.html)

Don't forget to install the fonts (or the install won't work).
sudo apt-get install xfonts-75dpi

whereis wkhtmltopdf then set the output of this command in you platform_options file at the knp_pdf_binary_path parameter.

Please store your pdf in the directory speficied in the claroline.param.pdf_directory parameter ($container->getParameter('...');)

usage: https://github.com/KnpLabs/KnpSnappyBundle

 
