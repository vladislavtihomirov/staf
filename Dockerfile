FROM python
COPY main.py all.csv ./
RUN pip3 install psycopg2
RUN python main.py

