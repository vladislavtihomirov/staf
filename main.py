import psycopg2
import csv


with open('all.csv', 'r') as file:
    data = list(csv.reader(file, delimiter=','))
    print('''Read data''')
    try:
        connection = psycopg2.connect(user="postgres",
                                      password="12345",
                                      host="127.0.0.1",
                                      port="5433",
                                      database="users")
        cur = connection.cursor()
        print('''Connected to DB''')
        cur.executemany('''INSERT INTO users_test (channel_id, device_type, tags, push_address, named_user_id, opt_in, installed, last_registration, created)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s) 
        ON CONFLICT ON CONSTRAINT channel_id DO UPDATE SET 
            device_type = EXCLUDED.device_type, 
            tags = EXCLUDED.tags, 
            push_address = EXCLUDED.push_address,
            named_user_id = EXCLUDED.named_user_id, 
            opt_in = EXCLUDED.opt_in, 
            installed = EXCLUDED.installed, 
            last_registration = EXCLUDED.last_registration, 
            created = EXCLUDED.created;
        ''', data)

        print('''Data executes''')
        connection.commit()
        print('''Data committed''')
        cur.close()
        connection.close()
        print("Connection is closed")
    except (Exception, psycopg2.Error) as error:
        print(error)