FROM python
COPY main.py all.csv ./
RUN python main.py

