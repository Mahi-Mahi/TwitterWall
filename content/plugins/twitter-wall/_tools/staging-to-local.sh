wget -O twitterwall.sql.gz http://twitterwall:jaeVe5ai@bnp-twitter-wall.web-staging.com/wp-content/plugins/bdd-export/bdd-export.php?bdd_export_key=5506e8a150704
# twitterwall:Xueng8we@
gunzip -f twitterwall.sql.gz
mysql -u root twitterwall < twitterwall.sql
say "done"


#rm twitterwall.sql
