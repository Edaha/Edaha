read -p "Are you sure? (yes/no) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]
    then
    php doctrine orm:schema-tool:drop --force
fi
