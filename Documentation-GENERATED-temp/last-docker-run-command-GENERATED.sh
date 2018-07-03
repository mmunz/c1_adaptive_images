docker run --rm --user=1000:1000 \
   -v /home/soma/Sites-docker/irdemo/web/typo3conf/ext/c1_adaptive_images:/PROJECT:ro \
   -v /home/soma/Sites-docker/irdemo/web/typo3conf/ext/c1_adaptive_images/Documentation-GENERATED-temp:/RESULT t3docs/render-documentation:v1.6.10-full makeall
